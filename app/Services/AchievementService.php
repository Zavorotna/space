<?php

namespace App\Services;

use App\Models\{User, Achievement};

class AchievementService
{
    protected CoinRewardService $coins;

    public function __construct(CoinRewardService $coins)
    {
        $this->coins = $coins;
    }

    public function checkLoginStreak(User $user): void
    {
        $streakMilestones = [10, 25, 50, 100, 250, 500, 1000];

        foreach ($streakMilestones as $milestone) {
            if ($user->login_streak >= $milestone) {
                $achievement = Achievement::where('type', 'login_streak')
                    ->where('threshold', $milestone)
                    ->first();

                if ($achievement && !$user->achievements()->where('achievement_id', $achievement->id)->exists()) {
                    $user->achievements()->attach($achievement->id, [
                        'coins_awarded' => $achievement->reward_coins,
                        'earned_at' => now(),
                    ]);
                    $this->coins->loginStreakReward($user, $milestone);
                }
            }
        }
    }

    public function grantAchievement(User $user, string $slug): bool
    {
        $achievement = Achievement::where('slug', $slug)->first();
        if (!$achievement) return false;

        if ($user->achievements()->where('achievement_id', $achievement->id)->exists()) {
            return false; // already earned
        }

        $user->achievements()->attach($achievement->id, [
            'coins_awarded' => $achievement->reward_coins,
            'earned_at' => now(),
        ]);

        if ($achievement->reward_coins > 0) {
            app(WalletService::class)->reward(
                $user,
                $achievement->reward_coins,
                "Досягнення: {$achievement->title}",
                Achievement::class,
                $achievement->id
            );
        }

        return true;
    }
}
