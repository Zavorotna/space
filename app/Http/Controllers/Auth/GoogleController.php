<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\{User, Wallet};
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    private function notifyAdmins(User $newUser): void
    {
        $notif = app(NotificationService::class);
        User::whereIn('role', ['admin', 'superadmin'])->each(function ($admin) use ($newUser, $notif) {
            $notif->notify(
                $admin,
                'new_registration',
                'Новий учасник зареєструвався',
                "{$newUser->full_name}" . ($newUser->phone ? " · {$newUser->phone}" : " · Google"),
                route('profile.show', $newUser)
            );
        });
    }

    // Link Google to an already logged-in account
    public function linkRedirect()
    {
        session(['google_link_user_id' => auth()->id()]);
        return Socialite::driver('google')->redirect();
    }

    public function unlinkGoogle(Request $request)
    {
        $user = $request->user();
        // Only unlink if user has another auth method (phone/password)
        if (!$user->phone && !$user->password) {
            return back()->with('error', 'Неможливо від\'язати Google — встановіть пароль або номер телефону для входу.');
        }
        $user->update(['google_id' => null]);
        return back()->with('success', 'Google акаунт від\'язано.');
    }

    public function callback(Request $request)
    {
        // User explicitly denied consent
        if ($request->has('error')) {
            $linkUserId = session('google_link_user_id');
            session()->forget('google_link_user_id');
            if ($linkUserId) {
                return redirect()->route('profile.edit')->with('error', 'Прив\'язку Google скасовано.');
            }
            return redirect()->route('register')
                ->with('google_error', 'Для входу через Google необхідно надати доступ до персональних даних. Або скористайтесь формою нижче.');
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            session()->forget('google_link_user_id');
            return redirect()->route('register')
                ->with('google_error', 'Не вдалося отримати дані від Google. Надайте доступ до персональних даних або скористайтесь формою нижче.');
        }

        // ── Link mode (user already logged in) ────────────────
        $linkUserId = session('google_link_user_id');
        session()->forget('google_link_user_id');

        if ($linkUserId) {
            $currentUser = User::find($linkUserId);
            if (!$currentUser) {
                return redirect()->route('profile.edit')->with('error', 'Помилка прив\'язки.');
            }
            $taken = User::where('google_id', $googleUser->getId())
                ->where('id', '!=', $currentUser->id)
                ->first();
            if ($taken) {
                return redirect()->route('profile.edit')
                    ->with('error', 'Цей Google акаунт вже прив\'язаний до іншого акаунту.');
            }
            $currentUser->update(['google_id' => $googleUser->getId()]);
            return redirect()->route('profile.edit')->with('success', 'Google акаунт прив\'язано.');
        }

        // ── Login / register mode ──────────────────────────────
        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if (!$user) {
            $nameParts = explode(' ', $googleUser->getName(), 2);
            $user = User::create([
                'first_name' => $nameParts[0] ?? '',
                'last_name'  => $nameParts[1] ?? '',
                'email'      => $googleUser->getEmail(),
                'google_id'  => $googleUser->getId(),
                'role'       => 'registered',
            ]);
            Wallet::create(['user_id' => $user->id, 'balance' => 0]);
            $this->notifyAdmins($user);
        } elseif (!$user->google_id) {
            $user->update(['google_id' => $googleUser->getId()]);
        }

        Auth::login($user, true);
        return redirect()->route('dashboard');
    }
}