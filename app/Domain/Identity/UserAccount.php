<?php

declare(strict_types=1);

namespace App\Domain\Identity;

use App\Enums\UserRole;
use InvalidArgumentException;

final class UserAccount
{
    private string $email;
    private string $displayName;
    private UserRole $role;
    private bool $isActive = true;

    public function __construct(
        public readonly string $id,
        string $email,
        string $displayName,
        UserRole $role,
        public readonly \DateTimeImmutable $createdAtUtc,
    ) {
        if (! \Illuminate\Support\Str::isUuid($id) || strtolower($id) === '00000000-0000-0000-0000-000000000000') {
            throw new InvalidArgumentException('User ID must be a non-empty UUID.');
        }

        $this->email = self::requireText($email, 'email');
        $this->displayName = self::requireText($displayName, 'displayName', 160);
        $this->role = $role;
    }

    public function email(): string { return $this->email; }
    public function displayName(): string { return $this->displayName; }
    public function role(): UserRole { return $this->role; }
    public function isActive(): bool { return $this->isActive; }

    public function changeRole(UserRole $role): void { $this->role = $role; }

    public function changeDisplayName(string $displayName): void
    {
        $this->displayName = self::requireText($displayName, 'displayName', 160);
    }

    public function deactivate(): void { $this->isActive = false; }
    public function activate(): void { $this->isActive = true; }

    private static function requireText(string $value, string $field, ?int $maxLength = null): string
    {
        $value = trim($value);
        if ($value === '') {
            throw new InvalidArgumentException("{$field} is required.");
        }
        if ($maxLength !== null && mb_strlen($value) > $maxLength) {
            throw new InvalidArgumentException("{$field} must not exceed {$maxLength} characters.");
        }
        return $value;
    }
}
