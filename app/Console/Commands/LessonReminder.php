<?php

namespace App\Console\Commands;

use App\Models\Lesson;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Sends push notifications 1 hour before each lesson.
 * Run every minute: * * * * * php artisan schedule:run
 * Or explicitly: * * * * * php artisan lessons:remind
 */
class LessonReminder extends Command
{
    protected $signature = 'lessons:remind';
    protected $description = 'Send push notifications 1 hour before lessons';

    public function handle(NotificationService $notificationService): void
    {
        $targetTime = Carbon::now()->addHour();
        $lessons = Lesson::with(['course.activeStudents', 'teacher'])
            ->where('date', today())
            ->where('start_time', '>=', $targetTime->format('H:i'))
            ->where('start_time', '<=', $targetTime->copy()->addMinutes(1)->format('H:i'))
            ->where('notification_sent', false)
            ->get();

        foreach ($lessons as $lesson) {
            // Notify teacher
            $notificationService->lessonReminder($lesson->teacher, $lesson);

            // Notify students
            foreach ($lesson->course->activeStudents as $student) {
                $notificationService->lessonReminder($student, $lesson);
            }

            $lesson->update(['notification_sent' => true]);
            $this->info("Reminder sent for lesson #{$lesson->id}: {$lesson->course->title}");
        }
    }
}
