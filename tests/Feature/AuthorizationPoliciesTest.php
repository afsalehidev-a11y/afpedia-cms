<?php

declare(strict_types=1);

namespace Tests\\Feature;

use App\\Enums\\UserRole;
use App\\Models\\Role;
use App\\Models\\User;
use App\\Support\\Identity\\CmsAuthorizationPolicies;
use Illuminate\\Foundation\\Testing\\RefreshDatabase;
use Illuminate\\Support\\Facades\\Gate;
use Illuminate\\Support\\Str;
use Tests\\TestCase;

final class AuthorizationPoliciesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\\Database\\Seeders\\CmsRoleSeeder::class);
    }

    public function test_active_manager_can_manage_the_installation(): void
    {
        $user = $this->userWithRole(UserRole::OrganizationManager);

        self::assertTrue(Gate::forUser($user)->allows(CmsAuthorizationPolicies::MANAGER_ONLY));
    }

    public function test_author_cannot_manage_the_installation(): void
    {
        $user = $this->userWithRole(UserRole::Author);

        self::assertFalse(Gate::forUser($user)->allows(CmsAuthorizationPolicies::MANAGER_ONLY));
    }

    public function test_inactive_manager_cannot_manage_or_edit_content(): void
    {
        $user = $this->userWithRole(UserRole::OrganizationManager, false);

        self::assertFalse(Gate::forUser($user)->allows(CmsAuthorizationPolicies::MANAGER_ONLY));
        self::assertFalse(Gate::forUser($user)->allows(CmsAuthorizationPolicies::CONTENT_EDITORS));
    }

    public function test_active_author_can_edit_content(): void
    {
        $user = $this->userWithRole(UserRole::Author);

        self::assertTrue(Gate::forUser($user)->allows(CmsAuthorizationPolicies::CONTENT_EDITORS));
    }

    private function userWithRole(UserRole $role, bool $active = true): User
    {
        $user = User::query()->create([
            'name' => $role->value,
            'email' => Str::lower($role->value).'-'.Str::uuid().'@example.org',
            'password' => 'ValidPassword123',
            'role' => $role,
            'is_active' => $active,
        ]);

        $assignedRole = Role::query()
            ->where('normalized_name', mb_strtoupper($role->value))
            ->firstOrFail();

        $user->roles()->attach($assignedRole);

        return $user;
    }
}
