<?php

declare(strict_types=1);

namespace App\Infrastructure\Identity;

use App\Application\Identity\UserAccountRepository;
use App\Domain\Identity\UserAccount;
use App\Enums\UserRole;
use App\Models\User;
use DateTimeImmutable;

final class EloquentUserAccountRepository implements UserAccountRepository
{
    public function getById(string $id): ?UserAccount
    {
        $user = User::query()->find($id);
        return $user ? $this->toDomain($user) : null;
    }

    public function getByEmail(string $normalizedEmail): ?UserAccount
    {
        $user = User::query()->where('email', mb_strtolower(trim($normalizedEmail)))->first();
        return $user ? $this->toDomain($user) : null;
    }

    public function add(UserAccount $account): void
    {
        $user = new User();
        $user->id = $account->id;
        $user->name = $account->displayName();
        $user->email = mb_strtolower(trim($account->email()));
        $user->role = $account->role();
        $user->is_active = $account->isActive();
        $user->created_at_utc = $account->createdAtUtc;
        $user->password = bin2hex(random_bytes(32));
        $user->save();
    }

    public function update(UserAccount $account): void
    {
        $user = User::query()->findOrFail($account->id);
        $user->name = $account->displayName();
        $user->email = mb_strtolower(trim($account->email()));
        $user->role = $account->role();
        $user->is_active = $account->isActive();
        $user->save();
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
