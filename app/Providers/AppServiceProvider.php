<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Identity\UserAccountRepository;
use App\Infrastructure\Identity\EloquentUserAccountRepository;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request): Limit {
            $email = mb_strtolower((string) $request->input('email'));

            return Limit::perMinutes(15, 10)->by($email.'|'.$request->ip());
        });
    }

    public function register(): void
    {
        $this->app->bind(UserAccountRepository::class, EloquentUserAccountRepository::class);
    }
}
