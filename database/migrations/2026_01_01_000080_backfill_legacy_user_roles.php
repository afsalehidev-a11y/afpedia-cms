<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration {
    private const ROLES = ['OrganizationManager', 'Author'];

    public function up(): void
    {
        $now = now();
        $roleIds = [];

        foreach (self::ROLES as $name) {
            $normalizedName = Str::upper($name);
            $existing = DB::table('roles')->where('normalized_name', $normalizedName)->first();

            if ($existing === null) {
                $id = (string) Str::uuid();
                DB::table('roles')->insert([
                    'id' => $id,
                    'name' => $name,
                    'normalized_name' => $normalizedName,
                    'display_name' => $name,
                    'is_system' => true,
                    'concurrency_stamp' => (string) Str::uuid(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $roleIds[$name] = $id;
            } else {
                $roleIds[$name] = (string) $existing->id;
            }
        }

        DB::table('users')->select(['id', 'role'])->orderBy('id')->chunk(200, function ($users) use ($roleIds): void {
            foreach ($users as $user) {
                $roleId = $roleIds[$user->role] ?? null;
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
        // Role membership backfill is intentionally irreversible: removing these
        // assignments could revoke access that was already in use after migration.
    }
};
