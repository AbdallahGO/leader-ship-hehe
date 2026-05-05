<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * TestUserSeeder
 * 
 * Creates test users with different states for testing various scenarios.
 * Useful for testing specific user conditions like banned, suspended, etc.
 * 
 * Usage in tests:
 *   $this->seed(TestUserSeeder::class);
 *   
 * Creates:
 *   - 1 admin user (admin@test.com)
 *   - 1 regular user (user@test.com)
 *   - 1 banned user (banned@test.com)
 *   - 1 suspended user (suspended@test.com)
 *   - 1 unverified user (unverified@test.com)
 *   - 10 additional random users
 */
class TestUserSeeder extends Seeder
{
    /**
     * Seed the test database with various user states.
     */
    public function run(): void
    {
        // First, ensure roles and permissions exist
        if (!\App\Models\Role::exists()) {
            $this->call(TestDataSeeder::class);
            return;
        }

        $adminRole = \App\Models\Role::where('name', 'Administrator')->first();
        $userRole = \App\Models\Role::where('name', 'User')->first();

        // Admin user
        $admin = User::factory()->create([
            'email' => 'admin@test.com',
            'name' => 'Test Admin',
        ]);
        if ($adminRole) {
            $admin->roles()->attach($adminRole);
        }

        // Regular user
        $user = User::factory()->create([
            'email' => 'user@test.com',
            'name' => 'Test User',
        ]);
        if ($userRole) {
            $user->roles()->attach($userRole);
        }

        // Banned user
        User::factory()->banned()->create([
            'email' => 'banned@test.com',
            'name' => 'Banned User',
        ]);

        // Suspended user
        User::factory()->suspended()->create([
            'email' => 'suspended@test.com',
            'name' => 'Suspended User',
        ]);

        // Unverified user
        User::factory()->unverified()->create([
            'email' => 'unverified@test.com',
            'name' => 'Unverified User',
        ]);

        // Additional random users
        User::factory()->count(10)->create();
    }
}
