<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

/**
 * PermissionSeeder
 *
 * Creates default permissions for the system.
 * Permissions are organized by category and resource.
 */
class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // User Management Permissions
            [
                'name' => 'View Users',
                'slug' => 'users.read',
                'category' => 'users',
                'description' => 'View all users and user profiles',
                'is_active' => true,
            ],
            [
                'name' => 'Create Users',
                'slug' => 'users.create',
                'category' => 'users',
                'description' => 'Create new user accounts',
                'is_active' => true,
            ],
            [
                'name' => 'Edit Users',
                'slug' => 'users.edit',
                'category' => 'users',
                'description' => 'Edit user information and settings',
                'is_active' => true,
            ],
            [
                'name' => 'Delete Users',
                'slug' => 'users.delete',
                'category' => 'users',
                'description' => 'Delete user accounts',
                'is_active' => true,
            ],
            [
                'name' => 'Ban Users',
                'slug' => 'users.ban',
                'category' => 'users',
                'description' => 'Ban and unban user accounts',
                'is_active' => true,
            ],
            [
                'name' => 'Suspend Users',
                'slug' => 'users.suspend',
                'category' => 'users',
                'description' => 'Suspend and unsuspend user accounts',
                'is_active' => true,
            ],

            // Role Management Permissions
            [
                'name' => 'View Roles',
                'slug' => 'roles.read',
                'category' => 'roles',
                'description' => 'View all roles and role details',
                'is_active' => true,
            ],
            [
                'name' => 'Create Roles',
                'slug' => 'roles.create',
                'category' => 'roles',
                'description' => 'Create new roles',
                'is_active' => true,
            ],
            [
                'name' => 'Edit Roles',
                'slug' => 'roles.edit',
                'category' => 'roles',
                'description' => 'Edit role information and permissions',
                'is_active' => true,
            ],
            [
                'name' => 'Delete Roles',
                'slug' => 'roles.delete',
                'category' => 'roles',
                'description' => 'Delete roles',
                'is_active' => true,
            ],

            // Permission Management
            [
                'name' => 'View Permissions',
                'slug' => 'permissions.read',
                'category' => 'permissions',
                'description' => 'View all permissions',
                'is_active' => true,
            ],
            [
                'name' => 'Assign Permissions',
                'slug' => 'permissions.assign',
                'category' => 'permissions',
                'description' => 'Assign permissions to roles',
                'is_active' => true,
            ],

            // Dashboard Access
            [
                'name' => 'Access Dashboard',
                'slug' => 'dashboard.access',
                'category' => 'dashboard',
                'description' => 'Access the admin/user dashboard',
                'is_active' => true,
            ],
            [
                'name' => 'View Analytics',
                'slug' => 'analytics.read',
                'category' => 'dashboard',
                'description' => 'View system analytics and statistics',
                'is_active' => true,
            ],
            [
                'name' => 'View Reports',
                'slug' => 'reports.read',
                'category' => 'dashboard',
                'description' => 'View system reports',
                'is_active' => true,
            ],

            // Content Management
            [
                'name' => 'Manage Content',
                'slug' => 'content.manage',
                'category' => 'content',
                'description' => 'Create and edit content',
                'is_active' => true,
            ],
            [
                'name' => 'Publish Content',
                'slug' => 'content.publish',
                'category' => 'content',
                'description' => 'Publish and unpublish content',
                'is_active' => true,
            ],
            [
                'name' => 'Delete Content',
                'slug' => 'content.delete',
                'category' => 'content',
                'description' => 'Delete content',
                'is_active' => true,
            ],

            // System Settings
            [
                'name' => 'Manage Settings',
                'slug' => 'settings.manage',
                'category' => 'system',
                'description' => 'Manage system settings',
                'is_active' => true,
            ],
            [
                'name' => 'View Logs',
                'slug' => 'logs.read',
                'category' => 'system',
                'description' => 'View system logs and activity logs',
                'is_active' => true,
            ],
        ];

        // Create permissions
        foreach ($permissions as $permissionData) {
            Permission::firstOrCreate(
                ['slug' => $permissionData['slug']],
                $permissionData
            );
        }

        $this->command->info('Permissions created successfully');
    }
}
