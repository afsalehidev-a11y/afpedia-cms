<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\User;
use App\Support\Identity\CmsAuthorizationPolicies;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

final class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::define(CmsAuthorizationPolicies::MANAGER_ONLY,
            fn (User $user): bool => $user->role === UserRole::OrganizationManager && $user->is_active);

        Gate::define(CmsAuthorizationPolicies::CONTENT_EDITORS,
            fn (User $user): bool => $user->is_active
                && in_array($user->role, [UserRole::OrganizationManager, UserRole::Author], true));
    }
}
