<?php

namespace App\Http\Controllers;

use App\Models\{User, Course, Location, Classroom, Achievement};
use Illuminate\Http\Request;

class AdminController extends Controller
{
    // ── User management ────────────────────────────────────────
    public function users(Request $request)
    {
        $users = User::when($request->role, fn($q, $r) => $q->where('role', $r))
            ->when($request->search, fn($q, $s) => $q->where(function ($q2) use ($s) {
                $q2->where('first_name', 'like', "%{$s}%")
                    ->orWhere('last_name', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%");
            }))
            ->latest()
            ->paginate(20);

        return view('admin.users', compact('users'));
    }

    public function updateUserRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => 'required|in:superadmin,admin,teacher,student,parent,registered',
        ]);
        $user->update($validated);
        return back()->with('success', "Роль змінено на {$validated['role']}.");
    }

    // ── Parent-child linking ───────────────────────────────────
    public function linkParent(Request $request)
    {
        $validated = $request->validate([
            'parent_id' => 'required|exists:users,id',
            'child_id' => 'required|exists:users,id',
        ]);

        $parent = User::findOrFail($validated['parent_id']);
        $child = User::findOrFail($validated['child_id']);

        if ($parent->role !== 'parent') {
            $parent->update(['role' => 'parent']);
        }

        $parent->children()->syncWithoutDetaching([$child->id]);
        return back()->with('success', "Зв'язок батько-дитина створено.");
    }

    // ── Locations & Classrooms ─────────────────────────────────
    public function locations()
    {
        $locations = Location::with('classrooms')->get();
        return view('admin.locations', compact('locations'));
    }

    public function storeLocation(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'work_start' => 'required|date_format:H:i',
            'work_end' => 'required|date_format:H:i|after:work_start',
        ]);
        Location::create($validated);
        return back()->with('success', 'Локацію створено.');
    }

    public function storeClassroom(Request $request, Location $location)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'nullable|integer|min:1',
        ]);
        $location->classrooms()->create($validated);
        return back()->with('success', 'Аудиторію створено.');
    }

    // ── Course FOP settings ────────────────────────────────────
    public function courseLiqpay(Request $request, Course $course)
    {
        $validated = $request->validate([
            'liqpay_merchant_id' => 'nullable|string',
            'liqpay_private_key' => 'nullable|string',
        ]);
        $course->update($validated);
        return back()->with('success', 'LiqPay налаштування збережено.');
    }

    // ── Trusted teacher ────────────────────────────────────────
    public function toggleTrustedTeacher(User $user)
    {
        $user->update(['is_trusted_teacher' => !$user->is_trusted_teacher]);
        return back()->with('success', 'Статус довіреного викладача змінено.');
    }

    // ── Achievements (seed) ────────────────────────────────────
    public function seedAchievements()
    {
        $streaks = [10, 25, 50, 100, 250, 500, 1000];
        $rewards = [10, 25, 50, 100, 250, 500, 500];

        foreach ($streaks as $i => $s) {
            Achievement::firstOrCreate(
                ['slug' => "streak_{$s}"],
                [
                    'title' => "Серія {$s} днів",
                    'description' => "Увійти {$s} днів поспіль",
                    'reward_coins' => $rewards[$i],
                    'type' => 'login_streak',
                    'threshold' => $s,
                ]
            );
        }

        return back()->with('success', 'Досягнення створено.');
    }
}
