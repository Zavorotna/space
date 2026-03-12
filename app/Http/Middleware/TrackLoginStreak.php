<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TrackLoginStreak
{
    public function handle(Request $request, Closure $next): mixed
    {
        $user = $request->user();
        if (!$user) return $next($request);

        $today = Carbon::today()->toDateString();
        if ($user->last_login_date === $today) {
            return $next($request);
        }

        $yesterday = Carbon::yesterday()->toDateString();
        if ($user->last_login_date === $yesterday) {
            $user->login_streak++;
        } else {
            $user->login_streak = 1;
        }

        if ($user->login_streak > $user->longest_streak) {
            $user->longest_streak = $user->login_streak;
        }

        $user->last_login_date = $today;
        $user->save();

        // Check streak achievements
        app(\App\Services\AchievementService::class)->checkLoginStreak($user);

        return $next($request);
    }
}
