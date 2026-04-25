<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function show(User $user)
    {
        if ($user->isTeacher()) {
            $user->load(['taughtCourses' => fn($q) => $q->published(), 'achievements', 'media']);
            return view('profile.teacher', compact('user'));
        }

        if ($user->isStudent()) {
            $user->load(['certificates.course', 'achievements', 'media', 'enrollments', 'parents']);
            return view('profile.student', compact('user'));
        }

        if ($user->isParent()) {
            $user->load(['children.certificates.course', 'children.achievements', 'certificates.course', 'achievements', 'media']);
            return view('profile.parent', compact('user'));
        }

        // admin / superadmin / registered — generic view
        $user->load(['achievements', 'media']);
        return view('profile.generic', compact('user'));
    }

    public function edit(Request $request)
    {
        $user = $request->user();
        return view('profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'phone'      => 'required|string|max:20|unique:users,phone,' . $user->id,
            'email'      => 'nullable|email|unique:users,email,' . $user->id,
            'bio'        => 'nullable|string|max:2000',
            'avatar'     => 'nullable|image|max:2048',
        ];

        // Birthday can only be set once
        if (is_null($user->birthday)) {
            $rules['birthday'] = 'required|date|before:today';
        }

        $validated = $request->validate($rules);

        $data = collect($validated)->except('avatar')->toArray();

        // If birthday already exists, never overwrite it
        if ($user->birthday) {
            unset($data['birthday']);
        }

        $user->update($data);

        if ($request->hasFile('avatar')) {
            $user->addMediaFromRequest('avatar')->toMediaCollection('avatar');
        }

        return back()->with('success', 'Профіль оновлено.');
    }

    public function changePassword(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'current_password' => ['required', function ($attr, $value, $fail) use ($user) {
                if (!Hash::check($value, $user->password)) {
                    $fail('Поточний пароль невірний.');
                }
            }],
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->update(['password' => $request->password]);

        return back()->with('password_success', 'Пароль змінено.');
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
