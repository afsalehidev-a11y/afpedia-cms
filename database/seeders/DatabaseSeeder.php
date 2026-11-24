<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(CmsRoleSeeder::class);
        // The installer creates the first manager securely; never ship a default password.
    }
}
