<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * DatabaseSeeder
 *
 * Main seeder that runs all other seeders.
 * Run with: php artisan db:seed
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed permissions first (required by roles)
        $this->call(PermissionSeeder::class);

        // Then seed roles and assign permissions
        $this->call(RoleSeeder::class);
    }
}
