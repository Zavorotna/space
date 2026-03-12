<?php

namespace App\Console\Commands;

use App\Models\Referral;
use App\Services\CoinRewardService;
use Illuminate\Console\Command;

/**
 * Process referral rewards at end of month.
 * Run monthly: 0 0 1 * * php artisan referrals:reward
 */
class ReferralRewards extends Command
{
    protected $signature = 'referrals:reward';
    protected $description = 'Award referral bonuses at end of month';

    public function handle(CoinRewardService $coins): void
    {
        $unrewarded = Referral::where('rewarded', false)
            ->with(['referrer', 'course'])
            ->get();

        foreach ($unrewarded as $ref) {
            if ($ref->course->type !== 'group') continue;

            $coins->referralReward($ref->referrer, $ref->course);
            $ref->update(['rewarded' => true]);
            $this->info("Referral reward: {$ref->referrer->full_name} for course {$ref->course->title}");
        }
    }
}
