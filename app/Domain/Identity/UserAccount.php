<?php

declare(strict_types=1);

namespace App\Domain\Identity;

use App\Enums\UserRole;
use InvalidArgumentException;

final class UserAccount
{
    public private(set) string $email;
    public private(set) string $displayName;
    public private(set) UserRole $role;
    public private(set) bool $isActive;

    public function __construct(
        public readonly string $id,
        string $email,
        string $displayName,
        UserRole $role,
        public readonly \DateTimeImmutable $createdAtUtc,
    ) {
        if ($id === '' || trim($id) === '') {
            throw new InvalidArgumentException('User ID cannot be empty.');
        }

        $this->email = self::requireText($email, 'email');
        $this->displayName = self::requireText($displayName, 'displayName');
        $this->role = $role;
        $this->isActive = true;
    }

    public function changeRole(UserRole $role): void
    {
        $this->role = $role;
    }

    public function changeDisplayName(string $displayName): void
    {
        $this->displayName = self::requireText($displayName, 'displayName');
    }

    public function deactivate(): void
    {
        $this->isActive = false;
    }

    public function activate(): void
    {
        $this->isActive = true;
    }

    private static function requireText(string $value, string $field): string
    {
        $value = trim($value);
        if ($value === '') {
            throw new InvalidArgumentException("{$field} is required.");
        }

        return $value;
    }
}
