<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Application\Identity\UserAccountRepository;
use App\Domain\Identity\UserAccount;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UserAccountRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_persists_and_retrieves_account_with_original_uuid(): void
    {
        $id = '11111111-1111-4111-8111-111111111111';
        $repository = $this->app->make(UserAccountRepository::class);
        $account = new UserAccount(
            $id,
            ' AUTHOR@example.org ',
            '  Author  ',
            UserRole::Author,
            new \DateTimeImmutable('2026-01-01T00:00:00+00:00'),
        );

        $repository->add($account);

        $byId = $repository->getById($id);
        self::assertNotNull($byId);
        self::assertSame($id, $byId->id);
        self::assertSame('author@example.org', $byId->email());
        self::assertSame('Author', $byId->displayName());
        self::assertSame('2026-01-01T00:00:00+00:00', $byId->createdAtUtc->format('Y-m-d\TH:i:sP'));
        self::assertSame($id, $repository->getByEmail('AUTHOR@example.org')?->id);

        self::assertTrue(User::query()->findOrFail($id)->hasCmsRole(UserRole::Author));
    }

    public function test_it_persists_role_and_activation_changes(): void
    {
        $id = '22222222-2222-4222-8222-222222222222';
        $repository = $this->app->make(UserAccountRepository::class);
        $account = new UserAccount(
            $id,
            'author@example.org',
            'Author',
            UserRole::Author,
            new \DateTimeImmutable('2026-01-01T00:00:00+00:00'),
        );
        $repository->add($account);

        $account->changeRole(UserRole::OrganizationManager);
        $account->changeDisplayName('Manager');
        $account->deactivate();
        $repository->update($account);

        $stored = $repository->getById($id);
        self::assertNotNull($stored);
        self::assertSame(UserRole::OrganizationManager, $stored->role());
        self::assertSame('Manager', $stored->displayName());
        self::assertFalse($stored->isActive());

        $storedUser = User::query()->findOrFail($id);
        self::assertTrue($storedUser->hasCmsRole(UserRole::OrganizationManager));
        self::assertFalse($storedUser->hasCmsRole(UserRole::Author));
    }
}
