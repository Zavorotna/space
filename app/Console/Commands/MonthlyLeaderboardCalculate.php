<?php

namespace App\Console\Commands;

use App\Models\{User, MonthlyLeaderboard, Course, TestAttempt, HomeworkSubmission, Attendance};
use App\Services\CoinRewardService;
use Illuminate\Console\Command;

/**
 * Calculates monthly leaderboard and awards top-3.
 * Run monthly: 0 0 1 * * php artisan leaderboard:calculate
 */
class MonthlyLeaderboardCalculate extends Command
{
    protected $signature = 'leaderboard:calculate {--month=} {--year=}';
    protected $description = 'Calculate monthly leaderboard and award top-3 coins';

    public function handle(CoinRewardService $coins): void
    {
        $month = $this->option('month') ?: now()->subMonth()->month;
        $year = $this->option('year') ?: now()->subMonth()->year;

        // Get all active students (including individual course students)
        $students = User::where('role', 'student')
            ->whereHas('enrollments', fn($q) => $q->where('status', 'active'))
            ->get();

        $scores = [];

        foreach ($students as $student) {
            $score = 0;

            // Test scores this month
            $score += TestAttempt::where('user_id', $student->id)
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->where('passed', true)
                ->sum('score') / 100;

            // Homework accepted this month
            $score += HomeworkSubmission::where('user_id', $student->id)
                ->whereYear('reviewed_at', $year)
                ->whereMonth('reviewed_at', $month)
                ->where('status', 'accepted')
                ->count() * 10;

            // Attendance this month
            $attendedLessons = Attendance::where('user_id', $student->id)
                ->where('is_present', true)
                ->whereHas('lesson', fn($q) => $q->whereYear('date', $year)->whereMonth('date', $month))
                ->count();
            $score += $attendedLessons * 2;

            // Login streak bonus
            $score += min($student->login_streak, 30);

            $scores[$student->id] = $score;
        }

        arsort($scores);

        $rank = 0;
        foreach ($scores as $userId => $score) {
            $rank++;
            MonthlyLeaderboard::updateOrCreate(
                ['user_id' => $userId, 'year' => $year, 'month' => $month],
                ['score' => $score, 'rank' => $rank]
            );

            // Award top-3
            if ($rank <= 3) {
                $reward = $coins->monthlyTopReward(User::find($userId), $rank);
                MonthlyLeaderboard::where('user_id', $userId)
                    ->where('year', $year)->where('month', $month)
                    ->update(['coins_awarded' => $reward]);
                $this->info("Top-{$rank}: User #{$userId} — {$score} pts, +{$reward} coins");
            }
        }

        $this->info("Leaderboard calculated: {$rank} students ranked for {$month}/{$year}");
    }
}
