<?php

namespace App\Services;

use App\Models\Role;
use App\Models\Permission;
use App\Models\User;

/**
 * RBAC Service
 *
 * Role-Based Access Control service for managing roles and permissions
 * Handles role/permission assignment and permission checking
 */
class RBACService
{
    /**
     * Create a new role
     */
    public function createRole(array $data): Role
    {
        return Role::create([
            'name' => $data['name'],
            'slug' => $data['slug'] ?? strtolower(str_replace(' ', '_', $data['name'])),
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    /**
     * Create a new permission
     */
    public function createPermission(array $data): Permission
    {
        return Permission::create([
            'name' => $data['name'],
            'slug' => $data['slug'] ?? strtolower(str_replace(' ', '_', $data['name'])),
            'description' => $data['description'] ?? null,
            'category' => $data['category'] ?? 'general',
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    /**
     * Assign role to user
     */
    public function assignRoleToUser(User $user, Role $role): void
    {
        if (!$user->roles()->where('role_id', $role->id)->exists()) {
            $user->roles()->attach($role);
        }
    }

    /**
     * Remove role from user
     */
    public function removeRoleFromUser(User $user, Role $role): void
    {
        $user->roles()->detach($role);
    }

    /**
     * Assign multiple roles to user
     */
    public function assignRolesToUser(User $user, array $roleIds): void
    {
        $user->roles()->sync($roleIds);
    }

    /**
     * Grant permission to role
     */
    public function grantPermissionToRole(Role $role, Permission $permission): void
    {
        if (!$role->permissions()->where('permission_id', $permission->id)->exists()) {
            $role->permissions()->attach($permission);
        }
    }

    /**
     * Revoke permission from role
     */
    public function revokePermissionFromRole(Role $role, Permission $permission): void
    {
        $role->permissions()->detach($permission);
    }

    /**
     * Grant multiple permissions to role
     */
    public function grantPermissionsToRole(Role $role, array $permissionIds): void
    {
        $role->permissions()->sync($permissionIds);
    }

    /**
     * Grant permission directly to user
     */
    public function grantPermissionToUser(User $user, Permission $permission): void
    {
        if (!$user->directPermissions()->where('permission_id', $permission->id)->exists()) {
            $user->directPermissions()->attach($permission);
        }
    }

    /**
     * Revoke permission from user
     */
    public function revokePermissionFromUser(User $user, Permission $permission): void
    {
        $user->directPermissions()->detach($permission);
    }

    /**
     * Check if user has permission (through role or directly)
     */
    public function userHasPermission(User $user, string $permissionSlug): bool
    {
        // Check direct permissions
        if ($user->directPermissions()->where('slug', $permissionSlug)->exists()) {
            return true;
        }

        // Check permissions through roles
        return $user->roles()
            ->whereHas('permissions', function ($query) use ($permissionSlug) {
                $query->where('slug', $permissionSlug);
            })
            ->exists();
    }

    /**
     * Check if user has any of given permissions
     */
    public function userHasAnyPermission(User $user, array $permissionSlugs): bool
    {
        foreach ($permissionSlugs as $slug) {
            if ($this->userHasPermission($user, $slug)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if user has all given permissions
     */
    public function userHasAllPermissions(User $user, array $permissionSlugs): bool
    {
        foreach ($permissionSlugs as $slug) {
            if (!$this->userHasPermission($user, $slug)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get all user permissions
     */
    public function getUserPermissions(User $user): array
    {
        $rolePermissions = $user->roles()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->pluck('slug')
            ->unique();

        $directPermissions = $user->directPermissions()
            ->pluck('slug');

        return $rolePermissions->merge($directPermissions)->toArray();
    }

    /**
     * Get all user roles
     */
    public function getUserRoles(User $user): array
    {
        return $user->roles()->pluck('slug')->toArray();
    }

    /**
     * Check if user has role
     */
    public function userHasRole(User $user, string $roleSlug): bool
    {
        return $user->roles()->where('slug', $roleSlug)->exists();
    }

    /**
     * Check if user has any role
     */
    public function userHasAnyRole(User $user, array $roleSlugs): bool
    {
        return $user->roles()->whereIn('slug', $roleSlugs)->exists();
    }

    /**
     * Check if user has all roles
     */
    public function userHasAllRoles(User $user, array $roleSlugs): bool
    {
        $count = $user->roles()->whereIn('slug', $roleSlugs)->count();
        return $count === count($roleSlugs);
    }

    /**
     * Update role
     */
    public function updateRole(Role $role, array $data): Role
    {
        $role->update($data);
        return $role;
    }

    /**
     * Update permission
     */
    public function updatePermission(Permission $permission, array $data): Permission
    {
        $permission->update($data);
        return $permission;
    }

    /**
     * Delete role
     */
    public function deleteRole(Role $role): bool
    {
        // Remove all users from role
        $role->users()->detach();
        // Remove all permissions from role
        $role->permissions()->detach();
        // Delete role
        return $role->delete();
    }

    /**
     * Delete permission
     */
    public function deletePermission(Permission $permission): bool
    {
        // Remove permission from all roles
        $permission->roles()->detach();
        // Remove permission from all users
        $permission->users()->detach();
        // Delete permission
        return $permission->delete();
    }

    /**
     * Get all roles
     */
    public function getAllRoles(): array
    {
        return Role::all()->toArray();
    }

    /**
     * Get active roles
     */
    public function getActiveRoles(): array
    {
        return Role::active()->get()->toArray();
    }

    /**
     * Get all permissions
     */
    public function getAllPermissions(): array
    {
        return Permission::all()->toArray();
    }

    /**
     * Get active permissions
     */
    public function getActivePermissions(): array
    {
        return Permission::active()->get()->toArray();
    }

    /**
     * Get permissions by category
     */
    public function getPermissionsByCategory(string $category): array
    {
        return Permission::byCategory($category)->get()->toArray();
    }

    /**
     * Create default roles
     */
    public function createDefaultRoles(): void
    {
        $roles = [
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Administrator with full access',
            ],
            [
                'name' => 'Moderator',
                'slug' => 'moderator',
                'description' => 'Moderator with limited admin access',
            ],
            [
                'name' => 'User',
                'slug' => 'user',
                'description' => 'Regular user',
            ],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['slug' => $role['slug']], $role);
        }
    }

    /**
     * Create default permissions
     */
    public function createDefaultPermissions(): void
    {
        $permissions = [
            // User permissions
            ['name' => 'View Users', 'slug' => 'users.read', 'category' => 'users'],
            ['name' => 'Create User', 'slug' => 'users.create', 'category' => 'users'],
            ['name' => 'Edit User', 'slug' => 'users.update', 'category' => 'users'],
            ['name' => 'Delete User', 'slug' => 'users.delete', 'category' => 'users'],
            ['name' => 'Ban User', 'slug' => 'users.ban', 'category' => 'users'],
            ['name' => 'Suspend User', 'slug' => 'users.suspend', 'category' => 'users'],

            // Role permissions
            ['name' => 'View Roles', 'slug' => 'roles.read', 'category' => 'roles'],
            ['name' => 'Create Role', 'slug' => 'roles.create', 'category' => 'roles'],
            ['name' => 'Edit Role', 'slug' => 'roles.update', 'category' => 'roles'],
            ['name' => 'Delete Role', 'slug' => 'roles.delete', 'category' => 'roles'],

            // Permission permissions
            ['name' => 'View Permissions', 'slug' => 'permissions.read', 'category' => 'permissions'],
            ['name' => 'Create Permission', 'slug' => 'permissions.create', 'category' => 'permissions'],
            ['name' => 'Edit Permission', 'slug' => 'permissions.update', 'category' => 'permissions'],
            ['name' => 'Delete Permission', 'slug' => 'permissions.delete', 'category' => 'permissions'],

            // Content permissions
            ['name' => 'View Content', 'slug' => 'content.read', 'category' => 'content'],
            ['name' => 'Create Content', 'slug' => 'content.create', 'category' => 'content'],
            ['name' => 'Edit Content', 'slug' => 'content.update', 'category' => 'content'],
            ['name' => 'Delete Content', 'slug' => 'content.delete', 'category' => 'content'],

            // System permissions
            ['name' => 'View Settings', 'slug' => 'settings.read', 'category' => 'system'],
            ['name' => 'Edit Settings', 'slug' => 'settings.update', 'category' => 'system'],
            ['name' => 'View Logs', 'slug' => 'logs.read', 'category' => 'system'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['slug' => $permission['slug']], $permission);
        }
    }

    /**
     * Assign all permissions to admin role
     */
    public function assignAllPermissionsToAdmin(): void
    {
        $adminRole = Role::where('slug', 'admin')->first();
        if ($adminRole) {
            $permissions = Permission::pluck('id')->toArray();
            $adminRole->syncPermissions($permissions);
        }
    }
}
