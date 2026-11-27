<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration {
    private const ROLE_IDS = [
        'OrganizationManager' => '7b5b5b3a-0a1b-4a01-8a01-000000000001',
        'Author' => '7b5b5b3a-0a1b-4a01-8a01-000000000002',
    ];

    public function up(): void
    {
        $now = now();

        foreach (self::ROLE_IDS as $name => $id) {
            DB::table('roles')->updateOrInsert(
                ['normalized_name' => Str::upper($name)],
                [
                    'id' => $id,
                    'name' => $name,
                    'normalized_name' => Str::upper($name),
                    'display_name' => $name,
                    'is_system' => true,
                    'concurrency_stamp' => (string) Str::uuid(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );
        }

        DB::table('users')->select(['id', 'role'])->orderBy('id')->chunk(200, function ($users): void {
            foreach ($users as $user) {
                $roleId = self::ROLE_IDS[$user->role] ?? null;
                if ($roleId === null) {
                    throw new RuntimeException("Cannot migrate user {$user->id}: unsupported CMS role '{$user->role}'.");
                }

                DB::table('user_roles')->insertOrIgnore([
                    'user_id' => $user->id,
                    'role_id' => $roleId,
                ]);
            }
        });
    }

    public function down(): void
    {
        DB::table('user_roles')->whereIn('role_id', array_values(self::ROLE_IDS))->delete();
        DB::table('roles')->whereIn('id', array_values(self::ROLE_IDS))->delete();
    }
};
