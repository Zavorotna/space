<?php

namespace App\Console\Commands;

use App\Models\{User, Course};
use App\Services\CoinRewardService;
use Illuminate\Console\Command;

/**
 * Check 100% attendance per month and award coins.
 * Run monthly: 0 0 1 * * php artisan attendance:reward
 */
class AttendanceRewards extends Command
{
    protected $signature = 'attendance:reward {--month=} {--year=}';
    protected $description = 'Award coins for 100% monthly attendance';

    public function handle(CoinRewardService $coins): void
    {
        $month = $this->option('month') ?: now()->subMonth()->month;
        $year = $this->option('year') ?: now()->subMonth()->year;

        $courses = Course::where('status', 'active')->with('activeStudents')->get();

        foreach ($courses as $course) {
            foreach ($course->activeStudents as $student) {
                $reward = $coins->attendanceReward($student, $course, $year, $month);
                if ($reward > 0) {
                    $this->info("{$student->full_name}: 100% attendance on {$course->title} — +{$reward}");
                }
            }
        }
    }
}
