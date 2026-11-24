<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class CmsRoleSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        DB::table('roles')->upsert([
            ['name' => 'organization_manager', 'display_name' => 'Organization manager', 'is_system' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'author', 'display_name' => 'Author', 'is_system' => true, 'created_at' => $now, 'updated_at' => $now],
        ], ['name'], ['display_name', 'is_system', 'updated_at']);
    }
}
