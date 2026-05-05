<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;

/**
 * RoleSeeder
 *
 * Creates default roles and assigns permissions to them.
 * Roles created: admin, moderator, user
 */
class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default roles
        $adminRole = Role::firstOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Administrator',
                'description' => 'Full system access with all permissions',
                'is_active' => true,
            ]
        );

        $moderatorRole = Role::firstOrCreate(
            ['slug' => 'moderator'],
            [
                'name' => 'Moderator',
                'description' => 'Content moderation and user management',
                'is_active' => true,
            ]
        );

        $userRole = Role::firstOrCreate(
            ['slug' => 'user'],
            [
                'name' => 'User',
                'description' => 'Regular user with basic permissions',
                'is_active' => true,
            ]
        );

        // Assign all permissions to admin
        $adminPermissions = Permission::where('is_active', true)->get();
        foreach ($adminPermissions as $permission) {
            $adminRole->permissions()->attach($permission->id);
        }

        // Assign moderation permissions to moderator
        $moderatorPermissions = Permission::where('is_active', true)
            ->whereIn('slug', [
                'users.read',
                'users.ban',
                'content.manage',
                'content.delete',
                'dashboard.access',
                'analytics.read',
            ])
            ->get();

        foreach ($moderatorPermissions as $permission) {
            $moderatorRole->permissions()->attach($permission->id);
        }

        // Assign basic permissions to user
        $userPermissions = Permission::where('is_active', true)
            ->whereIn('slug', [
                'content.manage',
                'dashboard.access',
            ])
            ->get();

        foreach ($userPermissions as $permission) {
            $userRole->permissions()->attach($permission->id);
        }

        $this->command->info('Roles created and permissions assigned successfully');
    }
}
