<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class CmsRoleSeeder extends Seeder
{
    private const ROLES = [
        ['id' => '7b5b5b3a-0a1b-4a01-8a01-000000000001', 'name' => 'OrganizationManager', 'display_name' => 'Organization manager'],
        ['id' => '7b5b5b3a-0a1b-4a01-8a01-000000000002', 'name' => 'Author', 'display_name' => 'Author'],
    ];

    public function run(): void
    {
        $now = now();
        foreach (self::ROLES as $role) {
            DB::table('roles')->updateOrInsert(
                ['normalized_name' => Str::upper($role['name'])],
                [
                    'id' => $role['id'],
                    'name' => $role['name'],
                    'normalized_name' => Str::upper($role['name']),
                    'display_name' => $role['display_name'],
                    'is_system' => true,
                    'concurrency_stamp' => (string) Str::uuid(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );
        }
    }
}
