<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\{CoinRewardService, NotificationService};
use Illuminate\Console\Command;

/**
 * Checks birthdays daily and awards coins.
 * Run daily: 0 8 * * * php artisan birthdays:reward
 */
class BirthdayRewards extends Command
{
    protected $signature = 'birthdays:reward';
    protected $description = 'Award birthday coins and send notifications';

    public function handle(CoinRewardService $coins, NotificationService $notif): void
    {
        $today     = now();
        $weekAhead = now()->addWeek();

        $admins = User::whereIn('role', ['superadmin', 'admin'])->get();

        // ── Today's birthdays ─────────────────────────────────────
        $todayBirthdays = User::whereMonth('birthday', $today->month)
            ->whereDay('birthday', $today->day)
            ->whereIn('role', ['student', 'teacher'])
            ->get();

        foreach ($todayBirthdays as $user) {
            // Award coins
            $coins->birthdayReward($user);

            // Greet the birthday person
            $notif->notify($user, 'birthday_today',
                '🎂 З днем народження!',
                'Hashtag Space вітає тебе! Нараховано бонусні монети.');

            // Notify admins
            foreach ($admins as $admin) {
                if ($admin->id === $user->id) continue;
                $notif->notify($admin, 'birthday_today',
                    'Сьогодні день народження!',
                    $user->full_name);
            }

            // If teacher — notify their active students
            if ($user->isTeacher()) {
                $studentIds = collect();
                foreach ($user->taughtCourses()->whereIn('status', ['active', 'enrolling'])->with('activeStudents')->get() as $course) {
                    foreach ($course->activeStudents as $student) {
                        if ($studentIds->contains($student->id)) continue;
                        $studentIds->push($student->id);
                        $notif->notify($student, 'birthday_today',
                            'Сьогодні день народження викладача!',
                            $user->full_name);
                    }
                }
            }

            $this->info("Birthday processed: {$user->full_name}");
        }

        // ── Upcoming birthdays (1 week ahead) ────────────────────
        $upcomingBirthdays = User::whereMonth('birthday', $weekAhead->month)
            ->whereDay('birthday', $weekAhead->day)
            ->whereIn('role', ['student', 'teacher'])
            ->get();

        foreach ($upcomingBirthdays as $bUser) {
            $dateLabel = $weekAhead->format('d.m');

            // Notify admins
            foreach ($admins as $admin) {
                $notif->notify($admin, 'birthday_upcoming',
                    'День народження через тиждень',
                    "{$bUser->full_name} — {$dateLabel}");
            }

            if ($bUser->isStudent()) {
                // Notify the student's teachers
                foreach ($bUser->activeEnrollments as $course) {
                    if ($course->teacher) {
                        $notif->notify($course->teacher, 'birthday_upcoming',
                            'День народження студента через тиждень',
                            "{$bUser->full_name} — {$dateLabel}");
                    }
                }
            }
        }
    }
}
