<?php

declare(strict_types=1);

namespace Tests\Feature;

use Database\Seeders\CmsRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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
}
