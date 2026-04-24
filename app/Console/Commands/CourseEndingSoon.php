<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Console\Command;

/**
 * Notify teacher and admins when a course ends in less than 1 month.
 * Run daily: 0 9 * * * php artisan courses:ending-soon
 */
class CourseEndingSoon extends Command
{
    protected $signature = 'courses:ending-soon';
    protected $description = 'Notify teachers and admins when a course ends in less than 1 month';

    public function handle(NotificationService $notifications): void
    {
        $courses = Course::whereNotNull('end_date')
            ->where('end_date', '>', now())
            ->where('end_date', '<=', now()->addMonth())
            ->where('status', 'active')
            ->with(['teacher', 'coTeachers', 'students'])
            ->get();

        foreach ($courses as $course) {
            $daysLeft = (int) now()->diffInDays($course->end_date);
            $message = "Курс «{$course->title}» завершується {$course->end_date->format('d.m.Y')} (залишилось {$daysLeft} дн.), студентів: {$course->students->count()}";
            $link = route('teacher.courses.edit', $course);

            // Notify main teacher
            if ($course->teacher) {
                $notifications->notify($course->teacher, 'course_ending_soon', 'Курс скоро завершується', $message, $link);
                $this->info("Notified teacher {$course->teacher->full_name} about: {$course->title}");
            }

            // Notify co-teachers
            foreach ($course->coTeachers as $coTeacher) {
                $notifications->notify($coTeacher, 'course_ending_soon', 'Курс скоро завершується', $message, $link);
            }

            // Notify admins
            User::whereIn('role', ['admin', 'superadmin'])->each(function ($admin) use ($notifications, $message, $link) {
                $notifications->notify($admin, 'course_ending_soon', 'Курс скоро завершується', $message, $link);
            });
        }

        $this->info("Processed {$courses->count()} courses ending soon.");
    }
}