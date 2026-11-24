<?php

declare(strict_types=1);

namespace App\Application\Identity;

use App\Domain\Identity\UserAccount;

interface UserAccountRepository
{
    public function getById(string $id): ?UserAccount;

    public function getByEmail(string $normalizedEmail): ?UserAccount;

    public function add(UserAccount $account): void;

    public function update(UserAccount $account): void;
}
