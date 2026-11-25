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

        Validator::make(
            ['name' => $name, 'email' => $email, 'password' => $password],
            [
                'name' => ['required', 'string', 'max:160'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
                'password' => CmsPasswordPolicy::rules(),
            ],
        )->validate();

        return DB::transaction(function () use ($name, $email, $password, $role): User {
            $user = User::query()->create([
                'name' => $name,
                'email' => $email,
                'password' => $password,
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
