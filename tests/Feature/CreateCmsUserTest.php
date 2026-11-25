<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Application\Identity\CreateCmsUser;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class CreateCmsUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_creation_enforces_the_csharp_password_policy(): void
    {
        try {
            app(CreateCmsUser::class)->handle(
                'Manager',
                'manager@example.org',
                'weak-password',
                UserRole::OrganizationManager,
            );
            $this->fail('A password without uppercase and digits must be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('password', $exception->errors());
        }

        $this->assertDatabaseMissing('users', ['email' => 'manager@example.org']);
    }

    public function test_user_creation_hashes_password_and_normalizes_email(): void
    {
        $user = app(CreateCmsUser::class)->handle(
            'Manager',
            ' MANAGER@EXAMPLE.ORG ',
            'StrongPassword123',
            UserRole::OrganizationManager,
        );

        $this->assertSame('manager@example.org', $user->email);
        $this->assertNotSame('StrongPassword123', $user->password);
        $this->assertTrue($user->is_active);
        $this->assertSame(UserRole::OrganizationManager, $user->role);
    }
}
