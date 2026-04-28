<?php

namespace App\Services;

use App\Models\{Lesson, Location, Classroom, User, Course};
use Carbon\Carbon;

class ScheduleService
{
    /**
     * Create a lesson with conflict checking
     */
    public function createLesson(array $data): Lesson
    {
        $this->validateLessonTime($data);

        if ($data['mode'] === 'offline') {
            if (Lesson::hasConflict($data['classroom_id'], $data['date'], $data['start_time'], $data['end_time'])) {
                throw new \Exception('Конфлікт розкладу: аудиторія зайнята в цей час.');
            }
        }

        if (Lesson::teacherHasConflict($data['teacher_id'], $data['date'], $data['start_time'], $data['end_time'])) {
            throw new \Exception('Конфлікт розкладу: викладач зайнятий в цей час.');
        }

        return Lesson::create($data);
    }

    /**
     * Validate lesson time against location working hours and teacher trust level
     */
    protected function validateLessonTime(array $data): void
    {
        $teacher = User::findOrFail($data['teacher_id']);

        if ($data['mode'] === 'offline' && isset($data['location_id'])) {
            $location = Location::findOrFail($data['location_id']);
            $start = Carbon::parse($data['start_time']);
            $end = Carbon::parse($data['end_time']);
            $workStart = Carbon::parse($location->work_start);
            $workEnd = Carbon::parse($location->work_end);

            if (!$teacher->is_trusted_teacher) {
                if ($start->lt($workStart) || $end->gt($workEnd)) {
                    throw new \Exception("Заняття поза робочим часом локації ({$location->work_start} - {$location->work_end}).");
                }
            }
        }
    }

    /**
     * Get schedule for a specific day
     */
    public function getDaySchedule(User $user, string $date): \Illuminate\Database\Eloquent\Collection
    {
        $query = Lesson::with(['course', 'location', 'classroom'])
            ->where('date', $date);

        if ($user->isTeacher()) {
            $query->where('teacher_id', $user->id);
        } elseif ($user->isStudent()) {
            $courseIds = $user->activeEnrollments()->pluck('courses.id');
            $query->whereIn('course_id', $courseIds);
        }

        return $query->orderBy('start_time')->get();
    }

    /**
     * Get schedule for a week
     */
    public function getWeekSchedule(User $user, string $startDate): \Illuminate\Database\Eloquent\Collection
    {
        $start = Carbon::parse($startDate)->startOfWeek();
        $end = $start->copy()->endOfWeek();

        $query = Lesson::with(['course', 'location', 'classroom'])
            ->whereBetween('date', [$start, $end]);

        if ($user->isTeacher()) {
            $query->where('teacher_id', $user->id);
        } elseif ($user->isStudent()) {
            $courseIds = $user->activeEnrollments()->pluck('courses.id');
            $query->whereIn('course_id', $courseIds);
        }

        return $query->orderBy('date')->orderBy('start_time')->get();
    }

    /**
     * Get schedule for a month
     */
    public function getMonthSchedule(User $user, int $year, int $month): \Illuminate\Database\Eloquent\Collection
    {
        $query = Lesson::with(['course', 'location', 'classroom'])
            ->whereYear('date', $year)
            ->whereMonth('date', $month);

        if ($user->isTeacher()) {
            $query->where('teacher_id', $user->id);
        } elseif ($user->isStudent()) {
            $courseIds = $user->activeEnrollments()->pluck('courses.id');
            $query->whereIn('course_id', $courseIds);
        }

        return $query->orderBy('date')->orderBy('start_time')->get();
    }

    /**
     * Auto-generate lessons for a course teacher based on course schedule fields.
     * Skips templates, skips dates where a lesson already exists for this teacher+course+date.
     * Returns the number of lessons created.
     */
    public function generateCourseLessons(Course $course, User $teacher): int
    {
        if ($course->is_template || !$course->hasSchedule()) {
            return 0;
        }

        $days       = array_map('intval', $course->schedule_days); // [1..7] ISO weekdays
        $startTime  = substr($course->schedule_start_time, 0, 5);
        $endTime    = substr($course->schedule_end_time, 0, 5);
        $mode       = $course->schedule_mode ?? 'online';
        $locationId = $course->schedule_location_id;
        $classroomId = $course->schedule_classroom_id;

        // Existing dates for this teacher+course to avoid duplicates
        $existing = Lesson::where('course_id', $course->id)
            ->where('teacher_id', $teacher->id)
            ->pluck('date')
            ->map(fn($d) => (string) $d)
            ->flip(); // use as a set for O(1) lookup

        $created = 0;
        $current = $course->start_date->copy();

        while ($current->lte($course->end_date)) {
            if (in_array($current->isoWeekday(), $days)) {
                $dateStr = $current->toDateString();

                if (!isset($existing[$dateStr])) {
                    Lesson::create([
                        'course_id'    => $course->id,
                        'teacher_id'   => $teacher->id,
                        'mode'         => $mode,
                        'location_id'  => $locationId,
                        'classroom_id' => $classroomId,
                        'date'         => $dateStr,
                        'start_time'   => $startTime,
                        'end_time'     => $endTime,
                    ]);
                    $created++;
                }
            }
            $current->addDay();
        }

        return $created;
    }

    /**
     * Confirm attendance for a lesson
     */
    public function confirmAttendance(Lesson $lesson, array $presentStudentIds): void
    {
        $courseStudents = $lesson->course->activeStudents()->pluck('users.id');

        foreach ($courseStudents as $studentId) {
            $isPresent = in_array($studentId, $presentStudentIds);

            $lesson->attendances()->updateOrCreate(
                ['user_id' => $studentId],
                ['is_present' => $isPresent]
            );

            // Notify parents if absent
            if (!$isPresent) {
                $student = User::find($studentId);
                if ($student) {
                    app(NotificationService::class)->absenceNotification($student, $lesson);
                }
            }
        }

        $lesson->update(['attendance_confirmed' => true]);
    }
}
