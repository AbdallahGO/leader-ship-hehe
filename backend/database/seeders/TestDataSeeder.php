<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Notification;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Session;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * TestDataSeeder
 * 
 * Creates base test data needed for testing.
 * Creates roles, permissions, users, and related test data.
 * 
 * Usage in tests:
 *   $this->seed(TestDataSeeder::class);
 */
class TestDataSeeder extends Seeder
{
    /**
     * Seed the test database.
     */
    public function run(): void
    {
        // Create roles
        $adminRole = Role::factory()->admin()->create();
        $moderatorRole = Role::factory()->moderator()->create();
        $userRole = Role::factory()->user()->create();

        // Create permissions for each category
        Permission::factory()->create(['name' => 'create-users', 'description' => 'Create user accounts']);
        Permission::factory()->create(['name' => 'read-users', 'description' => 'View user information']);
        Permission::factory()->create(['name' => 'update-users', 'description' => 'Update user details']);
        Permission::factory()->create(['name' => 'delete-users', 'description' => 'Delete user accounts']);
        Permission::factory()->create(['name' => 'create-posts', 'description' => 'Create new posts']);
        Permission::factory()->create(['name' => 'read-posts', 'description' => 'View posts']);
        Permission::factory()->create(['name' => 'update-posts', 'description' => 'Edit posts']);
        Permission::factory()->create(['name' => 'delete-posts', 'description' => 'Delete posts']);
        Permission::factory()->create(['name' => 'manage-roles', 'description' => 'Manage user roles']);
        Permission::factory()->create(['name' => 'manage-permissions', 'description' => 'Manage system permissions']);

        $permissions = Permission::all();

        // Assign permissions to roles
        $adminRole->permissions()->attach($permissions);
        $moderatorRole->permissions()->attach($permissions->slice(0, 8));
        $userRole->permissions()->attach($permissions->where('name', 'read-posts'));

        // Create test users
        $adminUser = User::factory()->create([
            'email' => 'admin@test.com',
            'name' => 'Admin User',
        ]);
        $adminUser->roles()->attach($adminRole);

        $moderatorUser = User::factory()->create([
            'email' => 'moderator@test.com',
            'name' => 'Moderator User',
        ]);
        $moderatorUser->roles()->attach($moderatorRole);

        $regularUser = User::factory()->create([
            'email' => 'user@test.com',
            'name' => 'Regular User',
        ]);
        $regularUser->roles()->attach($userRole);

        // Create regular test users
        User::factory()->count(5)->create();

        // Create sessions for users
        Session::factory()->count(3)->for($adminUser)->create();
        Session::factory()->count(2)->for($moderatorUser)->create();
        Session::factory()->count(1)->for($regularUser)->create();

        // Create notifications
        Notification::factory()->count(10)->create();

        // Create activity logs
        ActivityLog::factory()->count(20)->create();
    }
}
