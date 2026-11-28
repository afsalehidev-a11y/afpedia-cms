<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Support\Identity\AspNetIdentityPasswordHasher;
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

    public function store(Request $request, AspNetIdentityPasswordHasher $aspNetHasher): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);
        $email = mb_strtolower(trim($credentials['email']));
        $normalizedEmail = mb_strtoupper($email);

        $authenticated = DB::transaction(function () use ($request, $email, $normalizedEmail, $credentials, $aspNetHasher): bool {
            $user = User::query()
                ->where(function ($query) use ($email, $normalizedEmail): void {
                    $query->where('normalized_email', $normalizedEmail)
                        ->orWhere(function ($legacyQuery) use ($email): void {
                            // Compatibility path for accounts created before normalized_email existed.
                            $legacyQuery->whereNull('normalized_email')
                                ->whereRaw('LOWER(email) = ?', [$email]);
                        });
                })
                ->lockForUpdate()
                ->first();

            if (! $user || ! $user->is_active) {
                return false;
            }

            if ($user->lockout_enabled && $user->lockout_end?->isFuture()) {
                return false;
            }

            $laravelPasswordMatches = Hash::check($credentials['password'], $user->password);
            $legacyPasswordMatches = ! $laravelPasswordMatches
                && is_string($user->password_hash)
                && $aspNetHasher->verify($user->password_hash, $credentials['password']);

            if (! $laravelPasswordMatches && ! $legacyPasswordMatches) {
                if ($user->lockout_enabled) {
                    $user->access_failed_count++;
                    if ($user->access_failed_count >= self::MAX_FAILED_ATTEMPTS) {
                        $user->lockout_end = now()->addMinutes(self::LOCKOUT_MINUTES);
                    }
                    $user->save();
                }

                return false;
            }

            $updates = [
                'access_failed_count' => 0,
                'lockout_end' => null,
            ];

            if ($legacyPasswordMatches || Hash::needsRehash($user->password)) {
                $updates['password'] = Hash::make($credentials['password']);
            }

            if ($legacyPasswordMatches) {
                $updates['password_hash'] = null;
            }

            $user->forceFill($updates)->save();
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
