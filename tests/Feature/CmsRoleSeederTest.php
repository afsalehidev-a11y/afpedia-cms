<?php

declare(strict_types=1);

namespace Tests\Feature;

use Database\Seeders\CmsRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

final class CmsRoleSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_the_two_predefined_roles_idempotently(): void
    {
        $seeder = new CmsRoleSeeder();
        $seeder->run();
        $seeder->run();

        self::assertSame(1, DB::table('roles')->where('name', 'OrganizationManager')->count());
        self::assertSame(1, DB::table('roles')->where('name', 'Author')->count());
        self::assertSame(2, DB::table('roles')->count());
    }

    public function test_it_preserves_existing_role_ids_and_user_memberships(): void
    {
        $roleId = (string) Str::uuid();
        DB::table('roles')->insert([
            'id' => $roleId,
            'name' => 'OrganizationManager',
            'normalized_name' => 'ORGANIZATIONMANAGER',
            'display_name' => 'Existing manager role',
            'is_system' => false,
            'concurrency_stamp' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $userId = (string) Str::uuid();
        DB::table('users')->insert([
            'id' => $userId,
            'name' => 'Existing User',
            'email' => 'existing@example.test',
            'password' => 'not-used',
            'role' => 'OrganizationManager',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('user_roles')->insert(['user_id' => $userId, 'role_id' => $roleId]);

        (new CmsRoleSeeder())->run();

        self::assertSame($roleId, DB::table('roles')->where('normalized_name', 'ORGANIZATIONMANAGER')->value('id'));
        self::assertDatabaseHas('user_roles', ['user_id' => $userId, 'role_id' => $roleId]);
        self::assertSame(1, DB::table('roles')->where('normalized_name', 'ORGANIZATIONMANAGER')->count());
    }
}
