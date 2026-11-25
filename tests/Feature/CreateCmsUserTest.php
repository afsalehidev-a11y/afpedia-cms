<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Application\Identity\CreateCmsUser;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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

    public function test_user_creation_populates_aspnet_identity_compatibility_fields(): void
    {
        $user = app(CreateCmsUser::class)->handle(
            'Manager',
            ' MANAGER@EXAMPLE.ORG ',
            'StrongPassword123',
            UserRole::OrganizationManager,
        );

        $this->assertSame('manager@example.org', $user->email);
        $this->assertSame('manager@example.org', $user->user_name);
        $this->assertSame('MANAGER@EXAMPLE.ORG', $user->normalized_user_name);
        $this->assertSame('MANAGER@EXAMPLE.ORG', $user->normalized_email);
        $this->assertFalse($user->email_confirmed);
        $this->assertNotSame('StrongPassword123', $user->password);
        $this->assertTrue(Hash::check('StrongPassword123', $user->password));
        $this->assertNull($user->password_hash);
        $this->assertNotEmpty($user->security_stamp);
        $this->assertNotEmpty($user->concurrency_stamp);
        $this->assertTrue($user->lockout_enabled);
        $this->assertSame(0, $user->access_failed_count);
        $this->assertNotNull($user->created_at_utc);
        $this->assertTrue($user->is_active);
        $this->assertSame(UserRole::OrganizationManager, $user->role);
    }
}
