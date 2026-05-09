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
    // ── Regular Google login/register ─────────────────────────

    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request)
    {
        if ($request->has('error')) {
            $linkUserId = session('google_link_user_id');
            session()->forget(['google_link_user_id', 'google_pending']);
            if ($linkUserId) {
                return redirect()->route('profile.edit')->with('error', 'Прив\'язку Google скасовано.');
            }
            return redirect()->route('register')
                ->with('google_error', 'Для входу через Google необхідно надати доступ до персональних даних.');
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception) {
            session()->forget(['google_link_user_id', 'google_pending']);
            return redirect()->route('register')
                ->with('google_error', 'Не вдалося отримати дані від Google.');
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

            // Only fill fields that are currently empty
            $updates = ['google_id' => $googleUser->getId()];
            if (empty($currentUser->email) && $googleUser->getEmail()) {
                $updates['email'] = $googleUser->getEmail();
            }
            $currentUser->update($updates);

            return redirect()->route('profile.edit')->with('success', 'Google акаунт прив\'язано.');
        }

        // ── Login mode ─────────────────────────────────────────
        $user = User::where('google_id', $googleUser->getId())
            ->orWhere(fn($q) => $q->whereNotNull('email')->where('email', $googleUser->getEmail()))
            ->first();

        if ($user) {
            // Update only google_id if missing; never overwrite existing fields
            if (!$user->google_id) {
                $user->update(['google_id' => $googleUser->getId()]);
            }
            Auth::login($user, true);
            return redirect()->route('dashboard');
        }

        // No match — store Google data in session and ask user to confirm identity
        $nameParts = explode(' ', $googleUser->getName(), 2);
        session([
            'google_pending' => [
                'id'         => $googleUser->getId(),
                'email'      => $googleUser->getEmail(),
                'first_name' => $nameParts[0] ?? '',
                'last_name'  => $nameParts[1] ?? '',
            ],
        ]);

        return redirect()->route('auth.google.claim');
    }

    // ── Claim existing account by phone ───────────────────────

    public function showClaimForm()
    {
        if (!session('google_pending')) {
            return redirect()->route('login');
        }
        return view('auth.google-claim');
    }

    public function processClaim(Request $request)
    {
        $pending = session('google_pending');
        if (!$pending) {
            return redirect()->route('login');
        }

        // "Create new account" button pressed
        if ($request->boolean('create_new')) {
            session()->forget('google_pending');
            $user = User::create([
                'first_name' => $pending['first_name'],
                'last_name'  => $pending['last_name'],
                'email'      => $pending['email'] ?: null,
                'google_id'  => $pending['id'],
                'role'       => 'registered',
            ]);
            Wallet::create(['user_id' => $user->id, 'balance' => 0]);
            $this->notifyAdmins($user);
            Auth::login($user, true);
            return redirect()->route('dashboard');
        }

        // Phone-based claim
        $request->validate(['phone' => 'required|string|max:20']);

        $normalized = $this->normalizePhone($request->phone);
        $user = User::whereNotNull('phone')->get()
            ->first(fn($u) => $this->normalizePhone($u->phone) === $normalized);

        if (!$user) {
            return back()->with('error', 'Акаунт з таким номером телефону не знайдено.');
        }

        // Check google_id not already used by another account
        $taken = User::where('google_id', $pending['id'])
            ->where('id', '!=', $user->id)
            ->first();
        if ($taken) {
            return back()->with('error', 'Цей Google акаунт вже прив\'язаний до іншого акаунту.');
        }

        // Link: update only google_id and fill empty fields
        $updates = ['google_id' => $pending['id']];
        if (empty($user->email) && $pending['email']) {
            $updates['email'] = $pending['email'];
        }
        $user->update($updates);

        session()->forget('google_pending');
        Auth::login($user, true);
        return redirect()->route('dashboard')->with('success', 'Google акаунт успішно прив\'язано до вашого акаунту.');
    }

    // ── Link Google to an already logged-in account ───────────

    public function linkRedirect()
    {
        session(['google_link_user_id' => auth()->id()]);
        return Socialite::driver('google')->redirect();
    }

    public function unlinkGoogle(Request $request)
    {
        $user = $request->user();
        if (!$user->phone && !$user->password) {
            return back()->with('error', 'Неможливо від\'язати Google — встановіть пароль або номер телефону для входу.');
        }
        $user->update(['google_id' => null]);
        return back()->with('success', 'Google акаунт від\'язано.');
    }

    // ── Helpers ───────────────────────────────────────────────

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        // Keep last 9 digits: 380501234567 → 501234567, 0501234567 → 501234567
        return substr($digits, -9);
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
}