<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_user_can_sign_in_and_session_is_regenerated(): void
    {
        $user = User::create([
            'name' => 'Manager',
            'email' => 'manager@example.org',
            'password' => 'correct-password',
            'role' => UserRole::OrganizationManager,
            'is_active' => true,
        ]);

        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'correct-password',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
        $this->assertSame(0, $user->fresh()->access_failed_count);
    }

    public function test_login_uses_normalized_email_for_case_insensitive_lookup(): void
    {
        $user = User::create([
            'name' => 'Manager',
            'email' => 'manager@example.org',
            'normalized_email' => 'MANAGER@EXAMPLE.ORG',
            'password' => 'correct-password',
            'role' => UserRole::OrganizationManager,
            'is_active' => true,
        ]);

        $this->post(route('login.store'), [
            'email' => '  MANAGER@EXAMPLE.ORG ',
            'password' => 'correct-password',
        ])->assertRedirect('/');

        $this->assertAuthenticatedAs($user);
    }

    public function test_legacy_user_without_normalized_email_can_still_sign_in(): void
    {
        $user = User::create([
            'name' => 'Legacy Manager',
            'email' => 'legacy@example.org',
            'normalized_email' => null,
            'password' => 'correct-password',
            'role' => UserRole::OrganizationManager,
            'is_active' => true,
        ]);

        $this->post(route('login.store'), [
            'email' => 'LEGACY@EXAMPLE.ORG',
            'password' => 'correct-password',
        ])->assertRedirect('/');

        $this->assertAuthenticatedAs($user);
    }

    public function test_inactive_user_cannot_sign_in(): void
    {
        User::create([
            'name' => 'Inactive',
            'email' => 'inactive@example.org',
            'password' => 'correct-password',
            'role' => UserRole::Author,
            'is_active' => false,
        ]);

        $this->post(route('login.store'), [
            'email' => 'inactive@example.org',
            'password' => 'correct-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_fifth_failed_password_attempt_locks_user_for_fifteen_minutes(): void
    {
        Carbon::setTestNow('2026-11-25 12:00:00');
        $user = User::create([
            'name' => 'Manager',
            'email' => 'locked@example.org',
            'password' => 'correct-password',
            'role' => UserRole::OrganizationManager,
            'is_active' => true,
        ]);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('login.store'), [
                'email' => $user->email,
                'password' => 'incorrect-password',
            ])->assertSessionHasErrors('email');
        }

        $user->refresh();
        $this->assertSame(5, $user->access_failed_count);
        $this->assertTrue($user->lockout_end->equalTo(now()->addMinutes(15)));

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'correct-password',
        ])->assertSessionHasErrors('email');
        $this->assertGuest();

        Carbon::setTestNow();
    }

    public function test_user_can_sign_in_after_lockout_expiry(): void
    {
        Carbon::setTestNow('2026-11-25 12:16:00');
        $user = User::create([
            'name' => 'Manager',
            'email' => 'expired-lockout@example.org',
            'password' => 'correct-password',
            'role' => UserRole::OrganizationManager,
            'is_active' => true,
        ]);
        $user->forceFill([
            'access_failed_count' => 5,
            'lockout_end' => now()->subSecond(),
        ])->save();

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'correct-password',
        ])->assertRedirect('/');

        $this->assertAuthenticatedAs($user);
        $this->assertSame(0, $user->fresh()->access_failed_count);
        $this->assertNull($user->fresh()->lockout_end);
        Carbon::setTestNow();
    }

    public function test_lockout_disabled_user_is_not_locked_by_failed_attempts(): void
    {
        $user = User::create([
            'name' => 'Manager',
            'email' => 'no-lockout@example.org',
            'password' => 'correct-password',
            'role' => UserRole::OrganizationManager,
            'is_active' => true,
            'lockout_enabled' => false,
        ]);

        for ($attempt = 0; $attempt < 6; $attempt++) {
            $this->post(route('login.store'), [
                'email' => $user->email,
                'password' => 'incorrect-password',
            ])->assertSessionHasErrors('email');
        }

        $this->assertSame(0, $user->fresh()->access_failed_count);
        $this->assertNull($user->fresh()->lockout_end);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'correct-password',
        ])->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    }

    public function test_successful_sign_in_resets_failed_attempts_and_lockout(): void
    {
        $user = User::create([
            'name' => 'Manager',
            'email' => 'reset@example.org',
            'password' => 'correct-password',
            'role' => UserRole::OrganizationManager,
            'is_active' => true,
        ]);
        $user->forceFill(['access_failed_count' => 3])->save();

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'correct-password',
        ])->assertRedirect('/');

        $this->assertSame(0, $user->fresh()->access_failed_count);
        $this->assertNull($user->fresh()->lockout_end);
    }

    public function test_legacy_aspnet_identity_password_is_upgraded_after_successful_login(): void
    {
        $password = 'LegacyPassword123';
        $salt = random_bytes(16);
        $iterations = 10000;
        $subkey = hash_pbkdf2('sha256', $password, $salt, $iterations, 32, true);
        $legacyHash = base64_encode("\x01".pack('N3', 1, $iterations, strlen($salt)).$salt.$subkey);

        $user = User::create([
            'name' => 'Legacy Manager',
            'email' => 'legacy-hash@example.org',
            'password' => 'temporary-laravel-password',
            'password_hash' => $legacyHash,
            'role' => UserRole::OrganizationManager,
            'is_active' => true,
        ]);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => $password,
        ])->assertRedirect('/');

        $this->assertAuthenticatedAs($user);
        $user->refresh();
        $this->assertTrue(Hash::check($password, $user->password));
        $this->assertNull($user->password_hash);
    }

    public function test_login_rate_limit_allows_ten_attempts_and_blocks_the_eleventh_per_email_and_ip(): void
    {
        $server = ['REMOTE_ADDR' => '192.0.2.10'];

        for ($attempt = 0; $attempt < 10; $attempt++) {
            $this->withServerVariables($server)->post(route('login.store'), [
                'email' => 'missing@example.org',
                'password' => 'incorrect-password',
            ])->assertSessionHasErrors('email');
        }

        $this->withServerVariables($server)->post(route('login.store'), [
            'email' => 'missing@example.org',
            'password' => 'incorrect-password',
        ])->assertTooManyRequests();
    }

    public function test_login_rate_limit_uses_a_separate_bucket_for_a_different_ip(): void
    {
        for ($attempt = 0; $attempt < 10; $attempt++) {
            $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.11'])->post(route('login.store'), [
                'email' => 'missing@example.org',
                'password' => 'incorrect-password',
            ])->assertSessionHasErrors('email');
        }

        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.12'])->post(route('login.store'), [
            'email' => 'missing@example.org',
            'password' => 'incorrect-password',
        ])->assertSessionHasErrors('email');
    }

    public function test_login_rate_limit_treats_email_case_insensitively(): void
    {
        for ($attempt = 0; $attempt < 10; $attempt++) {
            $this->post(route('login.store'), [
                'email' => 'missing@example.org',
                'password' => 'incorrect-password',
            ])->assertSessionHasErrors('email');
        }

        $this->post(route('login.store'), [
            'email' => 'MISSING@example.org',
            'password' => 'incorrect-password',
        ])->assertTooManyRequests();
    }

    public function test_user_can_sign_out(): void
    {
        $user = User::create([
            'name' => 'Author',
            'email' => 'author@example.org',
            'password' => 'correct-password',
            'role' => UserRole::Author,
            'is_active' => true,
        ]);

        $this->actingAs($user)->post(route('logout'))->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
