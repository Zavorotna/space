<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    protected $fillable = [
        'course_id', 'teacher_id', 'title', 'mode', 'location_id', 'classroom_id',
        'date', 'start_time', 'end_time', 'attendance_confirmed', 'notification_sent',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'attendance_confirmed' => 'boolean',
            'notification_sent' => 'boolean',
        ];
    }

    public function course() { return $this->belongsTo(Course::class); }
    public function teacher() { return $this->belongsTo(User::class, 'teacher_id'); }
    public function location() { return $this->belongsTo(Location::class); }
    public function classroom() { return $this->belongsTo(Classroom::class); }
    public function attendances() { return $this->hasMany(Attendance::class); }

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
