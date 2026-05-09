<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\{User, Wallet};
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisterController extends Controller
{
    private function notifyAdmins(User $newUser): void
    {
        $notif = app(NotificationService::class);
        User::whereIn('role', ['admin', 'superadmin'])->each(function ($admin) use ($newUser, $notif) {
            $notif->notify(
                $admin,
                'new_registration',
                'Новий учасник зареєструвався',
                "{$newUser->full_name} · {$newUser->phone}",
                route('profile.show', $newUser)
            );
        });
    }

    public function showForm()
    {
        return response(view('auth.register'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate')
            ->header('Pragma', 'no-cache');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:users',
            'birthday' => 'required|date|before:today',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'phone' => $validated['phone'],
            'birthday' => $validated['birthday'],
            'password' => Hash::make($validated['password']),
            'role' => 'registered',
        ]);

        // Create wallet
        Wallet::create(['user_id' => $user->id, 'balance' => 0]);

        // Notify admins/superadmins about new registration
        $this->notifyAdmins($user);

        Auth::login($user);

        return redirect()->route('home');
    }
}
