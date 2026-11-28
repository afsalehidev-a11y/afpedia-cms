<?php

declare(strict_types=1);

namespace App\Infrastructure\Identity;

use App\Application\Identity\UserAccountRepository;
use App\Domain\Identity\UserAccount;
use App\Enums\UserRole;
use App\Models\Role;
use App\Models\User;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class EloquentUserAccountRepository implements UserAccountRepository
{
    public function getById(string $id): ?UserAccount
    {
        $user = User::query()->find($id);
        return $user ? $this->toDomain($user) : null;
    }

    public function getByEmail(string $normalizedEmail): ?UserAccount
    {
        $email = mb_strtolower(trim($normalizedEmail));
        $user = User::query()
            ->where('normalized_email', mb_strtoupper($email))
            ->orWhere('email', $email)
            ->first();

        return $user ? $this->toDomain($user) : null;
    }

    public function add(UserAccount $account): void
    {
        DB::transaction(function () use ($account): void {
            $email = mb_strtolower(trim($account->email()));
            $normalizedEmail = mb_strtoupper($email);
            $user = new User();
            $user->id = $account->id;
            $user->name = $account->displayName();
            $user->user_name = $email;
            $user->normalized_user_name = $normalizedEmail;
            $user->email = $email;
            $user->normalized_email = $normalizedEmail;
            $user->email_confirmed = false;
            $user->security_stamp = (string) Str::uuid();
            $user->concurrency_stamp = (string) Str::uuid();
            $user->role = $account->role();
            $user->is_active = $account->isActive();
            $user->created_at_utc = $account->createdAtUtc;
            $user->password = bin2hex(random_bytes(32));
            $user->save();

            $user->roles()->sync([$this->roleId($account->role())]);
        });
    }

    public function update(UserAccount $account): void
    {
        DB::transaction(function () use ($account): void {
            $user = User::query()->findOrFail($account->id);
            $email = mb_strtolower(trim($account->email()));
            $normalizedEmail = mb_strtoupper($email);
            $user->name = $account->displayName();
            $user->user_name = $email;
            $user->normalized_user_name = $normalizedEmail;
            $user->email = $email;
            $user->normalized_email = $normalizedEmail;
            $user->role = $account->role();
            $user->is_active = $account->isActive();
            $user->concurrency_stamp = (string) Str::uuid();
            $user->save();

            $user->roles()->sync([$this->roleId($account->role())]);
        });
    }

    private function roleId(UserRole $role): string
    {
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

        return (string) $cmsRole->getKey();
    }

    private function toDomain(User $user): UserAccount
    {
        $account = new UserAccount(
            (string) $user->getKey(),
            (string) $user->email,
            (string) $user->name,
            $user->role instanceof UserRole ? $user->role : UserRole::from((string) $user->role),
            new DateTimeImmutable($user->created_at_utc?->toISOString() ?? $user->created_at?->toISOString() ?? 'now'),
        );

        if (! $user->is_active) {
            $account->deactivate();
        }

        return $account;
    }
}
