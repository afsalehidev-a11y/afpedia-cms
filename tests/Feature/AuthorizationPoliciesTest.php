<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use App\Support\Identity\CmsAuthorizationPolicies;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

final class AuthorizationPoliciesTest extends TestCase
{
    public function test_active_manager_can_manage_the_installation(): void
    {
        $user = new User(['name' => 'Manager', 'email' => 'manager@example.org', 'role' => UserRole::OrganizationManager, 'is_active' => true]);
        self::assertTrue(Gate::forUser($user)->allows(CmsAuthorizationPolicies::MANAGER_ONLY));
    }

    public function test_author_cannot_manage_the_installation(): void
    {
        $user = new User(['name' => 'Author', 'email' => 'author@example.org', 'role' => UserRole::Author, 'is_active' => true]);
        self::assertFalse(Gate::forUser($user)->allows(CmsAuthorizationPolicies::MANAGER_ONLY));
    }

    public function test_inactive_manager_cannot_manage_or_edit_content(): void
    {
        $user = new User(['name' => 'Inactive', 'email' => 'inactive@example.org', 'role' => UserRole::OrganizationManager, 'is_active' => false]);
        self::assertFalse(Gate::forUser($user)->allows(CmsAuthorizationPolicies::MANAGER_ONLY));
        self::assertFalse(Gate::forUser($user)->allows(CmsAuthorizationPolicies::CONTENT_EDITORS));
    }

    public function test_active_author_can_edit_content(): void
    {
        $user = new User(['name' => 'Author', 'email' => 'author@example.org', 'role' => UserRole::Author, 'is_active' => true]);
        self::assertTrue(Gate::forUser($user)->allows(CmsAuthorizationPolicies::CONTENT_EDITORS));
    }
}
