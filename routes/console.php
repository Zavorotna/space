<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Every minute: lesson reminders
Schedule::call(fn() => Artisan::call('lessons:remind'))->everyMinute();

// Daily at 08:00: birthday rewards
Schedule::call(fn() => Artisan::call('birthdays:reward'))->dailyAt('08:00');

// Daily at midnight: VIP and resume expiration
Schedule::call(fn() => Artisan::call('vip:check'))->daily();
Schedule::call(fn() => Artisan::call('resumes:expire'))->daily();

// Daily at 09:00: courses ending soon
Schedule::call(fn() => Artisan::call('courses:ending-soon'))->dailyAt('09:00');

// Monthly on 1st: leaderboard, referrals, attendance
Schedule::call(fn() => Artisan::call('leaderboard:calculate'))->monthlyOn(1, '00:00');
Schedule::call(fn() => Artisan::call('referrals:reward'))->monthlyOn(1, '00:10');
Schedule::call(fn() => Artisan::call('attendance:reward'))->monthlyOn(1, '00:20');

/*
 * CRON SETUP:
 * * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
 */
