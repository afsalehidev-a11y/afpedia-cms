<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
