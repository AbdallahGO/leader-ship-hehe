<?php

namespace App\Services;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Eloquent\Collection;

/**
 * RoleService
 *
 * Service class for managing roles and role-related operations.
 * Provides methods for creating, updating, deleting, and querying roles.
 */
class RoleService
{
    /**
     * Create a new role
     */
    public function create(array $data): Role
    {
        // Generate slug from name if not provided
        if (!isset($data['slug'])) {
            $data['slug'] = $this->generateSlug($data['name']);
        }

        return Role::create($data);
    }

    /**
     * Update a role
     */
    public function update(Role $role, array $data): Role
    {
        // Update slug if name is changed
        if (isset($data['name']) && $data['name'] !== $role->name) {
            $data['slug'] = $this->generateSlug($data['name']);
        }

        $role->update($data);
        return $role;
    }

    /**
     * Delete a role
     */
    public function delete(Role $role): bool
    {
        return $role->delete();
    }

    /**
     * Get all roles
     */
    public function getAll(): Collection
    {
        return Role::with('permissions')->get();
    }

    /**
     * Get active roles
     */
    public function getActive(): Collection
    {
        return Role::where('is_active', true)
            ->with('permissions')
            ->get();
    }

    /**
     * Get role by ID
     */
    public function getById(int $roleId): ?Role
    {
        return Role::with('permissions', 'users')->find($roleId);
    }

    /**
     * Get role by slug
     */
    public function getBySlug(string $slug): ?Role
    {
        return Role::where('slug', $slug)
            ->with('permissions')
            ->first();
    }

    /**
     * Get role by name
     */
    public function getByName(string $name): ?Role
    {
        return Role::where('name', $name)
            ->with('permissions')
            ->first();
    }

    /**
     * Assign permission to role
     */
    public function assignPermission(Role $role, Permission $permission): void
    {
        if (!$role->permissions()->where('permission_id', $permission->id)->exists()) {
            $role->permissions()->attach($permission->id);
        }
    }

    /**
     * Assign multiple permissions to role
     */
    public function assignPermissions(Role $role, array $permissionIds): void
    {
        $role->permissions()->syncWithoutDetaching($permissionIds);
    }

    /**
     * Remove permission from role
     */
    public function removePermission(Role $role, Permission $permission): void
    {
        $role->permissions()->detach($permission->id);
    }

    /**
     * Remove all permissions from role
     */
    public function removeAllPermissions(Role $role): void
    {
        $role->permissions()->detach();
    }

    /**
     * Sync permissions for role (replace all)
     */
    public function syncPermissions(Role $role, array $permissionIds): void
    {
        $role->permissions()->sync($permissionIds);
    }

    /**
     * Check if role has permission
     */
    public function hasPermission(Role $role, string $permissionSlug): bool
    {
        return $role->hasPermission($permissionSlug);
    }

    /**
     * Get permission count for role
     */
    public function getPermissionCount(Role $role): int
    {
        return $role->permissions()->count();
    }

    /**
     * Get user count for role
     */
    public function getUserCount(Role $role): int
    {
        return $role->users()->count();
    }

    /**
     * Generate slug from name
     */
    private function generateSlug(string $name): string
    {
        return strtolower(str_replace(' ', '-', $name));
    }

    /**
     * Activate role
     */
    public function activate(Role $role): Role
    {
        $role->update(['is_active' => true]);
        return $role;
    }

    /**
     * Deactivate role
     */
    public function deactivate(Role $role): Role
    {
        $role->update(['is_active' => false]);
        return $role;
    }

    /**
     * Check if role is active
     */
    public function isActive(Role $role): bool
    {
        return $role->is_active === true;
    }
}
