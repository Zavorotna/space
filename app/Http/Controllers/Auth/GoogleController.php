<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\{User, Wallet};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request)
    {
        // User explicitly denied consent
        if ($request->has('error')) {
            return redirect()->route('register')
                ->with('google_error', 'Для входу через Google необхідно надати доступ до персональних даних. Або скористайтесь формою нижче.');
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('register')
                ->with('google_error', 'Не вдалося отримати дані від Google. Надайте доступ до персональних даних або скористайтесь формою нижче.');
        }

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
        } elseif (!$user->google_id) {
            $user->update(['google_id' => $googleUser->getId()]);
        }

        Auth::login($user, true);
        return redirect()->route('dashboard');
    }
}