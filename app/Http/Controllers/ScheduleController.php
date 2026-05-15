<?php

namespace App\Http\Controllers;

use App\Models\{Lesson, Location, Classroom, CalendarEvent, PlatformNotification, User};
use App\Services\ScheduleService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    protected ScheduleService $schedule;

    public function __construct(ScheduleService $schedule)
    {
        $this->schedule = $schedule;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $mode = $request->get('mode', 'week'); // day, week, month
        $date = $request->get('date', today()->toDateString());

        $lessons = match($mode) {
            'day' => $this->schedule->getDaySchedule($user, $date),
            'week' => $this->schedule->getWeekSchedule($user, $date),
            'month' => $this->schedule->getMonthSchedule($user, Carbon::parse($date)->year, Carbon::parse($date)->month),
        };

        $locations = Location::where('is_active', true)->with('classrooms')->get();

        return view('schedule.index', compact('lessons', 'mode', 'date', 'locations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'title' => 'nullable|string|max:255',
            'mode' => 'required|in:online,offline',
            'location_id' => 'required_if:mode,offline|nullable|exists:locations,id',
            'classroom_id' => 'required_if:mode,offline|nullable|exists:classrooms,id',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        $user = $request->user();
        $validated['teacher_id'] = $user->isTeacher() ? $user->id : $request->input('teacher_id', $user->id);

        try {
            $lesson = $this->schedule->createLesson($validated);
            return back()->with('success', 'Заняття додано.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function update(Request $request, Lesson $lesson)
    {
        $this->authorizeLesson($lesson);
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'mode' => 'required|in:online,offline',
            'location_id' => 'required_if:mode,offline|nullable|exists:locations,id',
            'classroom_id' => 'required_if:mode,offline|nullable|exists:classrooms,id',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        if ($validated['mode'] === 'offline' && isset($validated['classroom_id'])) {
            if (Lesson::hasConflict($validated['classroom_id'], $validated['date'], $validated['start_time'], $validated['end_time'], $lesson->id)) {
                return back()->with('error', 'Конфлікт: аудиторія зайнята.');
            }
        }

        $lesson->update($validated);
        return back()->with('success', 'Заняття оновлено.');
    }

    public function destroy(Lesson $lesson)
    {
        $this->authorizeLesson($lesson);
        $lesson->delete();
        return back()->with('success', 'Заняття видалено.');
    }

    public function cancelLesson(Request $request, Lesson $lesson)
    {
        $this->authorizeLesson($lesson);

        $endAt = Carbon::parse($lesson->date->format('Y-m-d') . ' ' . $lesson->end_time);
        if ($endAt->isPast()) {
            return back()->with('error', 'Заняття вже відбулось — використайте звіт про заняття.');
        }
        if ($lesson->completion_status) {
            return back()->with('error', 'Заняття вже має статус і не може бути скасоване повторно.');
        }

        $request->validate(['reason' => 'required|string|max:1000']);

        $lesson->update([
            'completion_status'   => 'cancelled',
            'completion_note'     => $request->reason,
            'completion_noted_at' => now(),
        ]);

        $this->notifyAdmins(
            "Заняття скасовано: {$lesson->course->title}",
            implode("\n", array_filter([
                "Викладач: {$request->user()->full_name}",
                "Курс: {$lesson->course->title}",
                "Дата: {$lesson->date->format('d.m.Y')} {$lesson->start_time}–{$lesson->end_time}",
                $lesson->location ? "Місце: {$lesson->location->name}" : null,
                "Причина: {$request->reason}",
            ]))
        );

        return back()->with('success', 'Заняття скасовано. Адміністраторів повідомлено.');
    }

    public function rescheduleLesson(Request $request, Lesson $lesson)
    {
        $this->authorizeLesson($lesson);

        $endAt = Carbon::parse($lesson->date->format('Y-m-d') . ' ' . $lesson->end_time);
        if ($endAt->isPast()) {
            return back()->with('error', 'Заняття вже відбулось — використайте звіт про заняття.');
        }
        if ($lesson->completion_status) {
            return back()->with('error', 'Заняття вже має статус і не може бути перенесено.');
        }

        $request->validate([
            'reason'          => 'required|string|max:1000',
            'new_date'        => 'required|date|after_or_equal:today',
            'new_start_time'  => 'required|date_format:H:i',
            'new_end_time'    => 'required|date_format:H:i|after:new_start_time',
        ]);

        $oldDate  = $lesson->date->format('d.m.Y');
        $oldTime  = substr($lesson->start_time, 0, 5) . '–' . substr($lesson->end_time, 0, 5);
        $newDate  = Carbon::parse($request->new_date)->format('d.m.Y');
        $newTime  = $request->new_start_time . '–' . $request->new_end_time;

        // Teacher conflict check (skip for self — same lesson)
        if (Lesson::teacherHasConflict(
            $lesson->teacher_id,
            $request->new_date,
            $request->new_start_time,
            $request->new_end_time,
            $lesson->id
        )) {
            return back()->with('error', 'Конфлікт: викладач вже зайнятий в цей час.');
        }

        $lesson->update([
            'date'       => $request->new_date,
            'start_time' => $request->new_start_time,
            'end_time'   => $request->new_end_time,
        ]);

        $this->notifyAdmins(
            "Заняття перенесено: {$lesson->course->title}",
            implode("\n", array_filter([
                "Викладач: {$request->user()->full_name}",
                "Курс: {$lesson->course->title}",
                "Було: {$oldDate} {$oldTime}",
                "Стало: {$newDate} {$newTime}",
                "Причина: {$request->reason}",
            ]))
        );

        return back()->with('success', 'Заняття перенесено. Адміністраторів повідомлено.');
    }

    private function authorizeLesson(Lesson $lesson): void
    {
        $user = auth()->user();
        if ($user->isAdmin()) return;
        if ($user->isTeacher() && $lesson->teacher_id === $user->id) return;
        if ($user->isTeacher() && $lesson->course->coTeachers()->where('user_id', $user->id)->exists()) return;
        abort(403);
    }

    private function notifyAdmins(string $title, string $message): void
    {
        User::whereIn('role', ['admin', 'superadmin'])->each(function ($admin) use ($title, $message) {
            PlatformNotification::create([
                'user_id' => $admin->id,
                'type'    => 'lesson_action',
                'title'   => $title,
                'message' => $message,
                'is_read' => false,
            ]);
        });
    }

    /**
     * Confirm attendance
     */
    public function confirmAttendance(Request $request, Lesson $lesson)
    {
        $presentIds = $request->input('present_students', []);
        $this->schedule->confirmAttendance($lesson, $presentIds);
        return back()->with('success', 'Присутність підтверджено.');
    }

    public function reportCompletion(Request $request, Lesson $lesson)
    {
        $isIndividual = $lesson->course->type === 'individual';

        $validated = $request->validate([
            'completion_status' => ['required', \Illuminate\Validation\Rule::in(
                $isIndividual ? ['full', 'partial', 'cancelled'] : ['full', 'cancelled', 'rescheduled']
            )],
            'actual_hours'     => 'nullable|numeric|min:0.5|max:10',
            'completion_note'  => 'nullable|string|max:1000',
            'schedule_makeup'  => 'boolean',
            'makeup_date'      => 'nullable|date|required_if:schedule_makeup,1',
            'makeup_start'     => 'nullable|date_format:H:i|required_if:schedule_makeup,1',
            'makeup_end'       => 'nullable|date_format:H:i|after:makeup_start|required_if:schedule_makeup,1',
        ]);

        $lesson->update([
            'completion_status'   => $validated['completion_status'],
            'actual_minutes'      => isset($validated['actual_hours']) ? (int) round($validated['actual_hours'] * 60) : null,
            'completion_note'     => $validated['completion_note'] ?? null,
            'completion_noted_at' => now(),
        ]);

        // Schedule makeup lesson if requested
        if (!empty($validated['schedule_makeup']) && !empty($validated['makeup_date'])) {
            $makeup = Lesson::create([
                'course_id'           => $lesson->course_id,
                'teacher_id'          => $lesson->teacher_id,
                'title'               => 'Відпрацювання: ' . ($lesson->title ?? $lesson->course->title),
                'mode'                => $lesson->mode,
                'location_id'         => $lesson->location_id,
                'classroom_id'        => $lesson->classroom_id,
                'date'                => $validated['makeup_date'],
                'start_time'          => $validated['makeup_start'],
                'end_time'            => $validated['makeup_end'],
                'makeup_for_lesson_id' => $lesson->id,
            ]);
        }

        return back()->with('success', 'Звіт про заняття збережено.');
    }

    public function lessonStats(Request $request)
    {
        $month = $request->integer('month', now()->month);
        $year  = $request->integer('year', now()->year);

        $lessons = Lesson::with(['course', 'teacher'])
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->whereNotNull('completion_status')
            ->orderBy('date')
            ->get();

        // Group by teacher
        $byTeacher = $lessons->groupBy('teacher_id')->map(function ($teacherLessons) {
            $individual = $teacherLessons->filter(fn($l) => $l->course->type === 'individual');
            $group      = $teacherLessons->filter(fn($l) => $l->course->type === 'group');

            return [
                'teacher'        => $teacherLessons->first()->teacher,
                'total'          => $teacherLessons->count(),
                'full'           => $teacherLessons->where('completion_status', 'full')->count(),
                'partial'        => $teacherLessons->where('completion_status', 'partial')->count(),
                'cancelled'      => $teacherLessons->where('completion_status', 'cancelled')->count(),
                'rescheduled'    => $teacherLessons->where('completion_status', 'rescheduled')->count(),
                'individual_minutes_planned' => $individual->sum(fn($l) => $l->plannedMinutes()),
                'individual_minutes_actual'  => $individual->sum(fn($l) => $l->actual_minutes ?? $l->plannedMinutes()),
                'lessons'        => $teacherLessons,
            ];
        });

        $unreported = Lesson::with(['course', 'teacher'])
            ->where('date', '<', today())
            ->whereNull('completion_status')
            ->orderBy('date')
            ->get();

        return view('superadmin.lesson-stats', compact('byTeacher', 'unreported', 'month', 'year'));
    }

    /**
     * JSON endpoint for calendar (AJAX)
     */
    public function calendarJson(Request $request)
    {
        $user = $request->user();
        $start = $request->get('start');
        $end = $request->get('end');

        $query = Lesson::with('course')
            ->whereBetween('date', [$start, $end]);

        if ($user->isTeacher()) {
            $query->where('teacher_id', $user->id);
        } elseif ($user->isStudent()) {
            $courseIds = $user->activeEnrollments()->pluck('id');
            $query->whereIn('course_id', $courseIds);
        }

        $lessons = $query->get()->map(fn($l) => [
            'id' => $l->id,
            'title' => $l->course->title . ($l->title ? ": {$l->title}" : ''),
            'date' => $l->date->toDateString(),
            'start_time' => $l->start_time,
            'end_time' => $l->end_time,
            'mode' => $l->mode,
        ]);

        return response()->json($lessons);
    }

    public function storeEvent(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'type'        => 'required|in:graduation,meeting,holiday,other',
            'date'        => 'required|date',
            'start_time'  => 'nullable|date_format:H:i',
            'end_time'    => 'nullable|date_format:H:i',
            'description' => 'nullable|string|max:1000',
        ]);

        CalendarEvent::create([...$validated, 'created_by' => $request->user()->id]);
        return back()->with('success', 'Подію додано.');
    }

    public function destroyEvent(CalendarEvent $event)
    {
        $event->delete();
        return back()->with('success', 'Подію видалено.');
    }
}
