<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

final class AuthenticatedSessionController
{
    private const MAX_FAILED_ATTEMPTS = 5;
    private const LOCKOUT_MINUTES = 15;

    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);
        $email = mb_strtolower(trim($credentials['email']));

        $authenticated = DB::transaction(function () use ($request, $email, $credentials): bool {
            $user = User::query()->where('email', $email)->lockForUpdate()->first();

            if (! $user || ! $user->is_active) {
                return false;
            }

            if ($user->lockout_enabled && $user->lockout_end?->isFuture()) {
                return false;
            }

            if (! Hash::check($credentials['password'], $user->password)) {
                if ($user->lockout_enabled) {
                    $user->access_failed_count++;
                    if ($user->access_failed_count >= self::MAX_FAILED_ATTEMPTS) {
                        $user->lockout_end = now()->addMinutes(self::LOCKOUT_MINUTES);
                    }
                    $user->save();
                }

                return false;
            }

            $user->forceFill([
                'access_failed_count' => 0,
                'lockout_end' => null,
            ])->save();
            Auth::login($user, $request->boolean('remember'));

            return true;
        });

        if (! $authenticated) {
            return back()->withErrors([
                'email' => 'اطلاعات ورود معتبر نیست، حساب غیرفعال است یا موقتاً قفل شده است.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended('/');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
