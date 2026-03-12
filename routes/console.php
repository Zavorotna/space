<?php

// app/Console/Kernel.php (For Laravel 10) or bootstrap/app.php schedule (Laravel 11)
// If using Laravel 11, add this to routes/console.php:

use Illuminate\Support\Facades\Schedule;

// Every minute: check for lessons starting in ~1 hour
Schedule::command('lessons:remind')->everyMinute();

// Daily at 8:00 AM: birthday checks
Schedule::command('birthdays:reward')->dailyAt('08:00');

// Daily at midnight: VIP and resume expiration
Schedule::command('vip:check')->daily();
Schedule::command('resumes:expire')->daily();

// Monthly on 1st at midnight: leaderboard, referrals, attendance
Schedule::command('leaderboard:calculate')->monthlyOn(1, '00:00');
Schedule::command('referrals:reward')->monthlyOn(1, '00:10');
Schedule::command('attendance:reward')->monthlyOn(1, '00:20');

/*
 * CRON SETUP:
 * Add this single entry to your server's crontab:
 * * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
 *
 * This runs every minute and Laravel's scheduler handles the rest.
 *
 * ALTERNATIVE (without Laravel scheduler, run each command separately):
 * * * * * * php /path/artisan lessons:remind
 * 0 8 * * * php /path/artisan birthdays:reward
 * 0 0 * * * php /path/artisan vip:check
 * 0 0 * * * php /path/artisan resumes:expire
 * 0 0 1 * * php /path/artisan leaderboard:calculate
 * 10 0 1 * * php /path/artisan referrals:reward
 * 20 0 1 * * php /path/artisan attendance:reward
 */
