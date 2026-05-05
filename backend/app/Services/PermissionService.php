<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;

/**
 * PermissionService
 *
 * Service class for managing permissions and permission-related operations.
 * Provides methods for creating, updating, and querying permissions.
 */
class PermissionService
{
    /**
     * Create a new permission
     */
    public function create(array $data): Permission
    {
        // Generate slug from name if not provided
        if (!isset($data['slug'])) {
            $data['slug'] = $this->generateSlug($data['name']);
        }

        return Permission::create($data);
    }

    /**
     * Update a permission
     */
    public function update(Permission $permission, array $data): Permission
    {
        // Update slug if name is changed
        if (isset($data['name']) && $data['name'] !== $permission->name) {
            $data['slug'] = $this->generateSlug($data['name']);
        }

        $permission->update($data);
        return $permission;
    }

    /**
     * Delete a permission
     */
    public function delete(Permission $permission): bool
    {
        return $permission->delete();
    }

    /**
     * Get all permissions
     */
    public function getAll(): Collection
    {
        return Permission::with('roles')->get();
    }

    /**
     * Get active permissions
     */
    public function getActive(): Collection
    {
        return Permission::where('is_active', true)
            ->with('roles')
            ->get();
    }

    /**
     * Get permissions by category
     */
    public function getByCategory(string $category): Collection
    {
        return Permission::where('category', $category)
            ->where('is_active', true)
            ->get();
    }

    /**
     * Get all categories
     */
    public function getCategories(): array
    {
        return Permission::distinct()
            ->pluck('category')
            ->filter()
            ->toArray();
    }

    /**
     * Get permission by ID
     */
    public function getById(int $permissionId): ?Permission
    {
        return Permission::find($permissionId);
    }

    /**
     * Get permission by slug
     */
    public function getBySlug(string $slug): ?Permission
    {
        return Permission::where('slug', $slug)->first();
    }

    /**
     * Get permission by name
     */
    public function getByName(string $name): ?Permission
    {
        return Permission::where('name', $name)->first();
    }

    /**
     * Check if user has permission
     */
    public function userHasPermission(User $user, string $permissionSlug): bool
    {
        return $user->hasPermission($permissionSlug);
    }

    /**
     * Check if user has any of given permissions
     */
    public function userHasAnyPermission(User $user, array $permissionSlugs): bool
    {
        return $user->hasAnyPermission($permissionSlugs);
    }

    /**
     * Check if user has all given permissions
     */
    public function userHasAllPermissions(User $user, array $permissionSlugs): bool
    {
        return $user->hasAllPermissions($permissionSlugs);
    }

    /**
     * Assign permission directly to user
     */
    public function assignToUser(User $user, Permission $permission): void
    {
        if (!$user->permissions()->where('permission_id', $permission->id)->exists()) {
            $user->permissions()->attach($permission->id);
        }
    }

    /**
     * Remove permission from user
     */
    public function removeFromUser(User $user, Permission $permission): void
    {
        $user->permissions()->detach($permission->id);
    }

    /**
     * Activate permission
     */
    public function activate(Permission $permission): Permission
    {
        $permission->update(['is_active' => true]);
        return $permission;
    }

    /**
     * Deactivate permission
     */
    public function deactivate(Permission $permission): Permission
    {
        $permission->update(['is_active' => false]);
        return $permission;
    }

    /**
     * Check if permission is active
     */
    public function isActive(Permission $permission): bool
    {
        return $permission->is_active === true;
    }

    /**
     * Get role count for permission
     */
    public function getRoleCount(Permission $permission): int
    {
        return $permission->roles()->count();
    }

    /**
     * Get user count for permission
     */
    public function getUserCount(Permission $permission): int
    {
        return $permission->users()->count();
    }

    /**
     * Generate slug from name
     */
    private function generateSlug(string $name): string
    {
        return strtolower(str_replace(' ', '.', $name));
    }

    /**
     * Create default permissions for common operations
     */
    public function createDefaultPermissions(): void
    {
        $permissions = [
            // User Management
            ['name' => 'View Users', 'slug' => 'users.read', 'category' => 'users', 'description' => 'View all users'],
            ['name' => 'Create Users', 'slug' => 'users.create', 'category' => 'users', 'description' => 'Create new users'],
            ['name' => 'Edit Users', 'slug' => 'users.edit', 'category' => 'users', 'description' => 'Edit user information'],
            ['name' => 'Delete Users', 'slug' => 'users.delete', 'category' => 'users', 'description' => 'Delete users'],
            ['name' => 'Ban Users', 'slug' => 'users.ban', 'category' => 'users', 'description' => 'Ban user accounts'],

            // Role Management
            ['name' => 'View Roles', 'slug' => 'roles.read', 'category' => 'roles', 'description' => 'View all roles'],
            ['name' => 'Create Roles', 'slug' => 'roles.create', 'category' => 'roles', 'description' => 'Create new roles'],
            ['name' => 'Edit Roles', 'slug' => 'roles.edit', 'category' => 'roles', 'description' => 'Edit role information'],
            ['name' => 'Delete Roles', 'slug' => 'roles.delete', 'category' => 'roles', 'description' => 'Delete roles'],

            // Permission Management
            ['name' => 'View Permissions', 'slug' => 'permissions.read', 'category' => 'permissions', 'description' => 'View all permissions'],
            ['name' => 'Assign Permissions', 'slug' => 'permissions.assign', 'category' => 'permissions', 'description' => 'Assign permissions to roles'],

            // Dashboard Access
            ['name' => 'Access Dashboard', 'slug' => 'dashboard.access', 'category' => 'dashboard', 'description' => 'Access admin dashboard'],
            ['name' => 'View Analytics', 'slug' => 'analytics.read', 'category' => 'dashboard', 'description' => 'View system analytics'],

            // Content Management
            ['name' => 'Manage Content', 'slug' => 'content.manage', 'category' => 'content', 'description' => 'Create and edit content'],
            ['name' => 'Publish Content', 'slug' => 'content.publish', 'category' => 'content', 'description' => 'Publish content'],
            ['name' => 'Delete Content', 'slug' => 'content.delete', 'category' => 'content', 'description' => 'Delete content'],
        ];

        foreach ($permissions as $permissionData) {
            if (!Permission::where('slug', $permissionData['slug'])->exists()) {
                Permission::create($permissionData);
            }
        }
    }
}
