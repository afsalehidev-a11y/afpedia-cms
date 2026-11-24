<?php

declare(strict_types=1);

namespace Tests\Unit\Identity;

use App\Domain\Identity\UserAccount;
use App\Enums\UserRole;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class UserAccountTest extends TestCase
{
    private function account(): UserAccount
    {
        return new UserAccount('user-1', 'author@example.org', 'Author', UserRole::Author, new \DateTimeImmutable('now'));
    }

    public function test_it_trims_values_and_starts_active(): void
    {
        $account = new UserAccount('user-1', ' manager@example.org ', ' Manager ', UserRole::OrganizationManager, new \DateTimeImmutable('now'));
        self::assertSame('manager@example.org', $account->email());
        self::assertSame('Manager', $account->displayName());
        self::assertTrue($account->isActive());
        self::assertSame(UserRole::OrganizationManager, $account->role());
    }

    public function test_it_can_be_deactivated_and_reactivated(): void
    {
        $account = $this->account();
        $account->deactivate();
        self::assertFalse($account->isActive());
        $account->activate();
        self::assertTrue($account->isActive());
    }

    public function test_it_can_change_role_and_display_name(): void
    {
        $account = $this->account();
        $account->changeRole(UserRole::OrganizationManager);
        $account->changeDisplayName('  Manager  ');
        self::assertSame(UserRole::OrganizationManager, $account->role());
        self::assertSame('Manager', $account->displayName());
    }

    public function test_it_rejects_empty_identity_and_text_fields(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new UserAccount('', 'x@example.org', 'X', UserRole::Author, new \DateTimeImmutable('now'));
    }

    public function test_it_rejects_blank_email_or_display_name(): void
    {
        try {
            new UserAccount('user-1', ' ', 'Author', UserRole::Author, new \DateTimeImmutable('now'));
            self::fail('Blank email should be rejected.');
        } catch (InvalidArgumentException) {
            self::assertTrue(true);
        }

        $this->expectException(InvalidArgumentException::class);
        new UserAccount('user-1', 'a@example.org', ' ', UserRole::Author, new \DateTimeImmutable('now'));
    }
}
