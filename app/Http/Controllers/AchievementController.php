<?php

namespace App\Http\Controllers;

use App\Models\{Achievement, MonthlyLeaderboard};
use Illuminate\Http\Request;

class AchievementController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $allAchievements = Achievement::all();
        $earned = $user->achievements()->pluck('achievement_id')->toArray();
        $leaderboard = MonthlyLeaderboard::where('year', now()->year)
            ->where('month', now()->month)
            ->with('user')
            ->orderBy('rank')
            ->limit(10)
            ->get();

        return view('achievements.index', compact('allAchievements', 'earned', 'leaderboard'));
    }
}
