<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Support\Identity\CmsRoleNames;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class CmsRoleSeeder extends Seeder
{
    public function run(): void
    {
        // Roles are represented by a constrained enum on users in this release.
        // This seeder deliberately validates the canonical role set without creating
        // an unnecessary role table or assigning a default account.
        if (count(CmsRoleNames::all()) !== 2) {
            throw new \LogicException('The predefined CMS role set is invalid.');
        }

        foreach (CmsRoleNames::all() as $role) {
            if (! in_array($role, ['organization_manager', 'author'], true)) {
                throw new \LogicException("Unsupported CMS role: {$role}");
            }
        }
    }
}
