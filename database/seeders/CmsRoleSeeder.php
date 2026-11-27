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
            $normalizedName = Str::upper($role['name']);
            $existing = DB::table('roles')->where('normalized_name', $normalizedName)->first();

            $values = [
                'name' => $role['name'],
                'normalized_name' => $normalizedName,
                'display_name' => $role['display_name'],
                'is_system' => true,
                'concurrency_stamp' => (string) Str::uuid(),
                'updated_at' => $now,
            ];

            if ($existing === null) {
                DB::table('roles')->insert([
                    'id' => $role['id'],
                    ...$values,
                    'created_at' => $now,
                ]);
                continue;
            }

            // Preserve IDs already referenced by user_roles.
            DB::table('roles')->where('id', $existing->id)->update($values);
        }
    }
}
