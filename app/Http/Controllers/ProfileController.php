<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(User $user)
    {
        if ($user->isTeacher()) {
            $user->load(['taughtCourses' => fn($q) => $q->published(), 'achievements', 'media']);
            return view('profile.teacher', compact('user'));
        }

        if ($user->isStudent()) {
            $user->load(['certificates.course', 'achievements', 'media']);
            return view('profile.student', compact('user'));
        }

        abort(404);
    }

    public function edit(Request $request)
    {
        $user = $request->user();
        return view('profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:users,phone,' . $user->id,
            'email' => 'nullable|email|unique:users,email,' . $user->id,
            'birthday' => 'required|date',
            'bio' => 'nullable|string|max:2000',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $user->update($validated);

        if ($request->hasFile('avatar')) {
            $user->addMediaFromRequest('avatar')->toMediaCollection('avatar');
        }

        return back()->with('success', 'Профіль оновлено.');
    }

    /**
     * VIP: upload extra avatars (up to 5)
     */
    public function uploadExtraAvatar(Request $request)
    {
        $user = $request->user();
        if (!$user->isVip()) return back()->with('error', 'Доступно лише для VIP.');

        $request->validate(['avatar' => 'required|image|max:2048']);
        $user->addMediaFromRequest('avatar')->toMediaCollection('extra_avatars');

        return back()->with('success', 'Аватар додано.');
    }
}
