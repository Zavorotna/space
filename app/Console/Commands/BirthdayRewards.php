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
        $today = now();

        // Find users with birthday today
        $users = User::whereMonth('birthday', $today->month)
            ->whereDay('birthday', $today->day)
            ->whereIn('role', ['student', 'teacher'])
            ->get();

        foreach ($users as $user) {
            $coins->birthdayReward($user);
            $this->info("Birthday reward for {$user->full_name}");
        }

        // Notify admins/teachers about upcoming birthdays (1 week ahead)
        $weekAhead = now()->addWeek();
        $upcomingBirthdays = User::whereMonth('birthday', $weekAhead->month)
            ->whereDay('birthday', $weekAhead->day)
            ->whereIn('role', ['student'])
            ->get();

        $adminsAndTeachers = User::whereIn('role', ['superadmin', 'admin'])->get();

        foreach ($upcomingBirthdays as $bUser) {
            // Notify admins
            foreach ($adminsAndTeachers as $admin) {
                $notif->notify($admin, 'birthday_upcoming',
                    "День народження через тиждень",
                    "{$bUser->full_name} — {$weekAhead->format('d.m')}");
            }

            // Notify student's teacher(s)
            foreach ($bUser->activeEnrollments as $course) {
                $notif->notify($course->teacher, 'birthday_upcoming',
                    "День народження студента через тиждень",
                    "{$bUser->full_name} — {$weekAhead->format('d.m')}");
            }
        }

        // Notify on the birthday itself
        foreach ($users as $bUser) {
            foreach ($adminsAndTeachers as $admin) {
                $notif->notify($admin, 'birthday_today',
                    "Сьогодні день народження!",
                    "{$bUser->full_name}");
            }
        }
    }
}
