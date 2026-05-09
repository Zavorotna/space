<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    protected $fillable = [
        'course_id', 'teacher_id', 'title', 'mode', 'location_id', 'classroom_id',
        'date', 'start_time', 'end_time', 'topic_ids', 'attendance_confirmed', 'notification_sent',
        'completion_status', 'actual_minutes', 'completion_note', 'completion_noted_at', 'makeup_for_lesson_id',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'topic_ids' => 'array',
            'attendance_confirmed' => 'boolean',
            'notification_sent' => 'boolean',
            'completion_noted_at' => 'datetime',
        ];
    }

    public function course() { return $this->belongsTo(Course::class); }
    public function teacher() { return $this->belongsTo(User::class, 'teacher_id'); }
    public function location() { return $this->belongsTo(Location::class); }
    public function classroom() { return $this->belongsTo(Classroom::class); }
    public function attendances() { return $this->hasMany(Attendance::class); }
    public function makeupFor() { return $this->belongsTo(Lesson::class, 'makeup_for_lesson_id'); }
    public function makeupLesson() { return $this->hasOne(Lesson::class, 'makeup_for_lesson_id'); }

    public function plannedMinutes(): int
    {
        if (!$this->start_time || !$this->end_time) {
            return 120;
        }
        $diff = (int) \Carbon\Carbon::parse($this->start_time)->diffInMinutes(\Carbon\Carbon::parse($this->end_time));
        return $diff > 0 ? $diff : 120;
    }

    public function needsCompletionReport(): bool
    {
        $endAt = \Carbon\Carbon::parse($this->date->format('Y-m-d') . ' ' . $this->end_time);
        return $endAt->isPast() && is_null($this->completion_status);
    }

    /**
     * Check for scheduling conflicts (same classroom/time or same teacher/time)
     */
    public static function hasConflict(int $classroomId, string $date, string $startTime, string $endTime, ?int $excludeId = null): bool
    {
        return static::where('classroom_id', $classroomId)
            ->where('date', $date)
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where(function ($q2) use ($startTime, $endTime) {
                    $q2->where('start_time', '<', $endTime)->where('end_time', '>', $startTime);
                });
            })
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->exists();
    }

    public static function teacherHasConflict(int $teacherId, string $date, string $startTime, string $endTime, ?int $excludeId = null): bool
    {
        return static::where('teacher_id', $teacherId)
            ->where('date', $date)
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where('start_time', '<', $endTime)->where('end_time', '>', $startTime);
            })
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->exists();
    }
}
