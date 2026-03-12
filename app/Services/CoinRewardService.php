<?php

namespace App\Services;

use App\Models\{User, Course, HomeworkSubmission, TestAttempt, GraduationSubmission, CourseReview, Attendance, BirthdayReward};
use Carbon\Carbon;

class CoinRewardService
{
    protected WalletService $wallet;

    public function __construct(WalletService $wallet)
    {
        $this->wallet = $wallet;
    }

    // ── Test rewards ───────────────────────────────────────────
    public function testReward(User $user, TestAttempt $attempt): int
    {
        if (!$attempt->passed) return 0;

        $coins = match($attempt->attempt_number) {
            1 => 40, 2 => 20, 3 => 10, default => 0,
        };

        if ($coins > 0) {
            $this->wallet->reward($user, $coins, "Тест з {$attempt->attempt_number}-ї спроби: +{$coins}", TestAttempt::class, $attempt->id);
            $attempt->update(['coins_awarded' => $coins]);
        }
        return $coins;
    }

    // Charge for retake
    public function testRetakeCharge(User $user, $test): void
    {
        $this->wallet->deduct($user, 10, 'penalty', 'Повторна здача тесту: -10', 'App\Models\Test', $test->id);
    }

    // ── Homework rewards ───────────────────────────────────────
    public function homeworkReward(User $user, HomeworkSubmission $submission): int
    {
        $hw = $submission->homework;
        $coins = $hw->reward_coins;

        // Deductions for revisions
        $deduction = $submission->revision_count; // -1 per revision
        $coins = max(0, $coins - $deduction);

        if ($coins > 0) {
            $this->wallet->reward($user, $coins, "Домашка '{$hw->title}': +{$coins}", HomeworkSubmission::class, $submission->id);
        }

        // Early submission bonus
        if ($submission->early_submission) {
            $this->wallet->reward($user, 10, 'Рання здача домашки: +10', HomeworkSubmission::class, $submission->id);
            $coins += 10;
        }

        $submission->update(['coins_awarded' => true]);
        return $coins;
    }

    // ── Graduation project rewards ─────────────────────────────
    public function graduationReward(User $user, GraduationSubmission $submission): int
    {
        $coins = $submission->calculateReward(); // starts at 100, -5 per revision, min 25

        if ($coins > 0) {
            $this->wallet->reward($user, $coins, "Випускний проєкт: +{$coins}", GraduationSubmission::class, $submission->id);
            $submission->update(['coins_awarded' => $coins]);
        }
        return $coins;
    }

    // ── Attendance reward (100% per month) ─────────────────────
    public function attendanceReward(User $user, Course $course, int $year, int $month): int
    {
        $lessons = $course->lessons()
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get();

        if ($lessons->isEmpty()) return 0;

        $attended = 0;
        foreach ($lessons as $lesson) {
            $att = $lesson->attendances()->where('user_id', $user->id)->first();
            if ($att && $att->is_present) $attended++;
        }

        if ($attended === $lessons->count()) {
            $this->wallet->reward($user, 25, "100% відвідуваність {$month}/{$year}: +25");
            return 25;
        }
        return 0;
    }

    // ── Course review reward ───────────────────────────────────
    public function reviewReward(User $user, CourseReview $review): int
    {
        $this->wallet->reward($user, 100, "Відгук про курс: +100", CourseReview::class, $review->id);
        return 100;
    }

    // ── Birthday reward ────────────────────────────────────────
    public function birthdayReward(User $user): void
    {
        $year = now()->year;

        // Per-course birthday reward
        foreach ($user->activeEnrollments as $course) {
            $exists = BirthdayReward::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->where('year', $year)
                ->exists();

            if (!$exists) {
                $amount = $user->isTeacher() ? 500 : 100;
                $this->wallet->reward($user, $amount, "День народження ({$course->title}): +{$amount}");
                BirthdayReward::create([
                    'user_id' => $user->id,
                    'course_id' => $course->id,
                    'year' => $year,
                    'coins_awarded' => $amount,
                ]);
            }
        }
    }

    // ── Referral reward (end of month) ─────────────────────────
    public function referralReward(User $user, Course $course): int
    {
        if ($course->type !== 'group') return 0;
        $this->wallet->reward($user, 200, "Реферал на курс '{$course->title}': +200");
        return 200;
    }

    // ── Monthly top rewards ────────────────────────────────────
    public function monthlyTopReward(User $user, int $rank): int
    {
        $coins = match($rank) {
            1 => 50, 2 => 30, 3 => 20, default => 0,
        };
        if ($coins > 0) {
            $this->wallet->reward($user, $coins, "Топ-{$rank} місяця: +{$coins}");
        }
        return $coins;
    }

    // ── Login streak rewards ───────────────────────────────────
    public function loginStreakReward(User $user, int $streak): int
    {
        $milestones = [10 => 10, 25 => 25, 50 => 50, 100 => 100, 250 => 250, 500 => 500, 1000 => 500];
        if (!isset($milestones[$streak])) return 0;

        $coins = $milestones[$streak];
        $this->wallet->reward($user, $coins, "Серія входів {$streak} днів: +{$coins}");
        return $coins;
    }
}
