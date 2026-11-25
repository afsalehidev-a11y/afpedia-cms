<?php

declare(strict_types=1);

namespace App\Application\Identity;

use App\Enums\UserRole;
use App\Models\Role;
use App\Models\User;
use App\Support\Identity\CmsPasswordPolicy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

final class CreateCmsUser
{
    public function handle(
        string $name,
        string $email,
        string $password,
        UserRole $role,
    ): User {
        $name = trim($name);
        $email = mb_strtolower(trim($email));
        $normalizedEmail = mb_strtoupper($email);
        $securityStamp = (string) Str::uuid();
        $concurrencyStamp = (string) Str::uuid();

        Validator::make(
            ['name' => $name, 'email' => $email, 'password' => $password],
            [
                'name' => ['required', 'string', 'max:160'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
                'password' => CmsPasswordPolicy::rules(),
            ],
        )->validate();

        return DB::transaction(function () use (
            $name, $email, $normalizedEmail, $password, $role,
            $securityStamp, $concurrencyStamp,
        ): User {
            $user = User::query()->create([
                'name' => $name,
                'user_name' => $email,
                'normalized_user_name' => $normalizedEmail,
                'email' => $email,
                'normalized_email' => $normalizedEmail,
                'email_confirmed' => false,
                'password' => $password,
                // Reserved for imported ASP.NET Identity hashes; never store plaintext here.
                'password_hash' => null,
                'security_stamp' => $securityStamp,
                'concurrency_stamp' => $concurrencyStamp,
                'lockout_enabled' => true,
                'access_failed_count' => 0,
                'created_at_utc' => now('UTC'),
                'role' => $role,
                'is_active' => true,
            ]);

            $roleName = $role->value;
            $cmsRole = Role::query()->firstOrCreate(
                ['normalized_name' => mb_strtoupper($roleName)],
                [
                    'id' => (string) Str::uuid(),
                    'name' => $roleName,
                    'display_name' => $roleName,
                    'is_system' => true,
                    'concurrency_stamp' => (string) Str::uuid(),
                ],
            );
            $user->roles()->sync([$cmsRole->getKey()]);

            return $user;
        });
    }
}
