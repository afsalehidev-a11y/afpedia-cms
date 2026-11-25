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
            'email' => 'legacy@example.org',
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
