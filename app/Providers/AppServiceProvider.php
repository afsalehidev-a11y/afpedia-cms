<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Identity\UserAccountRepository;
use App\Infrastructure\Identity\EloquentUserAccountRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserAccountRepository::class, EloquentUserAccountRepository::class);
    }
}
