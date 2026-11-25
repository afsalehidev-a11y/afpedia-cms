<?php

declare(strict_types=1);

namespace App\Application\Identity;

use App\Enums\UserRole;
use App\Models\User;
use App\Support\Identity\CmsPasswordPolicy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

final class CreateCmsUser
{
    public function handle(
        string $name,
        string $email,
        string $password,
        UserRole $role,
    ): User {
        Validator::make(
            ['name' => $name, 'email' => $email, 'password' => $password],
            [
                'name' => ['required', 'string', 'max:160'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
                'password' => CmsPasswordPolicy::rules(),
            ],
        )->validate();

        return DB::transaction(fn (): User => User::query()->create([
            'name' => trim($name),
            'email' => mb_strtolower(trim($email)),
            'password' => $password,
            'role' => $role,
            'is_active' => true,
        ]));
    }
}
