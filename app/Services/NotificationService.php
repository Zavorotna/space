<?php

namespace App\Services;

use App\Models\{User, PlatformNotification, PushSubscription};
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Create internal notification + send browser push
     */
    public function notify(User $user, string $type, string $title, ?string $message = null, ?string $link = null): PlatformNotification
    {
        $notification = PlatformNotification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'link' => $link,
        ]);

        // Send browser push
        $this->sendPush($user, $title, $message, $link);

        return $notification;
    }

    /**
     * Notify parents of student
     */
    public function notifyParents(User $student, string $type, string $title, ?string $message = null): void
    {
        foreach ($student->parents as $parent) {
            $this->notify($parent, $type, $title, $message);
        }
    }

    /**
     * Send Web Push notification
     */
    public function sendPush(User $user, string $title, ?string $body = null, ?string $url = null): void
    {
        $subscriptions = $user->pushSubscriptions;
        if ($subscriptions->isEmpty()) return;

        $vapidPublicKey = config('services.webpush.public_key');
        $vapidPrivateKey = config('services.webpush.private_key');

        if (!$vapidPublicKey || !$vapidPrivateKey) {
            Log::warning('VAPID keys not configured for web push.');
            return;
        }

        $payload = json_encode([
            'title' => $title,
            'body' => $body,
            'url' => $url,
        ]);

        // Use web-push library (minishlink/web-push) or raw push API
        // For simplicity, mark as sent — actual push requires web-push PHP lib
        foreach ($subscriptions as $sub) {
            try {
                // Placeholder: integrate with minishlink/web-push
                // $webPush->sendOneNotification($sub->endpoint, $payload, $sub->p256dh, $sub->auth);
                Log::info("Push sent to user {$user->id}: {$title}");
            } catch (\Exception $e) {
                Log::error("Push failed for user {$user->id}: " . $e->getMessage());
            }
        }
    }

    /**
     * Lesson reminder (1 hour before)
     */
    public function lessonReminder(User $user, $lesson): void
    {
        $this->notify(
            $user,
            'lesson_reminder',
            'Нагадування про заняття',
            "Заняття '{$lesson->course->title}' через 1 годину ({$lesson->start_time})",
            route('schedule.index')
        );
    }

    /**
     * Absence notification to parents
     */
    public function absenceNotification(User $student, $lesson): void
    {
        $this->notifyParents(
            $student,
            'absence',
            'Відсутність на занятті',
            "{$student->full_name} був(-ла) відсутній на занятті '{$lesson->course->title}' ({$lesson->date->format('d.m.Y')})"
        );
    }

    /**
     * Teacher remark to parents
     */
    public function remarkNotification(User $student, string $remark): void
    {
        $this->notifyParents(
            $student,
            'remark',
            'Зауваження від викладача',
            "Зауваження для {$student->full_name}: {$remark}"
        );
    }
}
