<?php

namespace App\Services;

use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\Paginator;

/**
 * UserManagementService
 *
 * Service class for managing users from an admin perspective.
 * Provides methods for user administration including banning, suspension, and role management.
 */
class UserManagementService
{
    protected RoleService $roleService;
    protected PermissionService $permissionService;

    public function __construct(
        RoleService $roleService,
        PermissionService $permissionService
    ) {
        $this->roleService = $roleService;
        $this->permissionService = $permissionService;
    }

    /**
     * Get all users with pagination
     */
    public function getAllUsers(int $page = 1, int $perPage = 15)
    {
        return User::with('roles', 'permissions')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get active users
     */
    public function getActiveUsers(): Collection
    {
        return User::where('is_banned', false)
            ->where('is_suspended', false)
            ->with('roles')
            ->get();
    }

    /**
     * Get banned users
     */
    public function getBannedUsers(): Collection
    {
        return User::where('is_banned', true)
            ->with('roles')
            ->get();
    }

    /**
     * Get suspended users
     */
    public function getSuspendedUsers(): Collection
    {
        return User::where('is_suspended', true)
            ->with('roles')
            ->get();
    }

    /**
     * Get user by ID with all relationships
     */
    public function getUserById(int $userId): ?User
    {
        return User::with('roles', 'permissions', 'activityLogs', 'sessions')
            ->find($userId);
    }

    /**
     * Ban a user
     */
    public function banUser(User $user, string $reason = null): User
    {
        $user->ban($reason);

        // Log the action
        $this->logAdminAction($user, "banned user {$user->id}" . ($reason ? ": $reason" : ''));

        return $user;
    }

    /**
     * Unban a user
     */
    public function unbanUser(User $user): User
    {
        $user->unban();

        // Log the action
        $this->logAdminAction($user, "unbanned user {$user->id}");

        return $user;
    }

    /**
     * Suspend a user
     */
    public function suspendUser(User $user, string $reason = null): User
    {
        $user->suspend($reason);

        // Log the action
        $this->logAdminAction($user, "suspended user {$user->id}" . ($reason ? ": $reason" : ''));

        return $user;
    }

    /**
     * Unsuspend a user
     */
    public function unsuspendUser(User $user): User
    {
        $user->unsuspend();

        // Log the action
        $this->logAdminAction($user, "unsuspended user {$user->id}");

        return $user;
    }

    /**
     * Update user role through RBAC system
     */
    public function updateUserRole(User $user, int $roleId): User
    {
        $role = $this->roleService->getById($roleId);

        if ($role) {
            // Clear existing roles and assign new one
            $user->roles()->sync($role->id);

            // Also update legacy role field if needed
            $user->update(['role' => $role->slug]);

            // Log the action
            $this->logAdminAction($user, "changed user {$user->id} role to {$role->name}");
        }

        return $user;
    }

    /**
     * Assign roles to user
     */
    public function assignRolesToUser(User $user, array $roleIds): User
    {
        $user->roles()->sync($roleIds);

        // Log the action
        $this->logAdminAction($user, "assigned roles to user {$user->id}");

        return $user;
    }

    /**
     * Update user information
     */
    public function updateUser(User $user, array $data): User
    {
        // Don't allow direct password update through this method
        if (isset($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        // Log the action
        $this->logAdminAction($user, "updated user {$user->id} information");

        return $user;
    }

    /**
     * Get user activity logs
     */
    public function getUserActivityLogs(User $user, int $limit = 50)
    {
        return $user->activityLogs()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get user statistics
     */
    public function getUserStatistics(): array
    {
        return [
            'total_users' => User::count(),
            'active_users' => User::where('is_banned', false)
                ->where('is_suspended', false)
                ->count(),
            'banned_users' => User::where('is_banned', true)->count(),
            'suspended_users' => User::where('is_suspended', true)->count(),
            'new_users_this_week' => User::where('created_at', '>=', now()->subWeek())->count(),
            'new_users_this_month' => User::where('created_at', '>=', now()->subMonth())->count(),
        ];
    }

    /**
     * Delete user account
     */
    public function deleteUser(User $user): bool
    {
        // Log the action before deletion
        $this->logAdminAction($user, "deleted user {$user->id}");

        // Delete user and all related data
        return $user->delete();
    }

    /**
     * Search users by name or email
     */
    public function searchUsers(string $query, int $page = 1, int $perPage = 15)
    {
        return User::where('name', 'like', "%$query%")
            ->orWhere('email', 'like', "%$query%")
            ->with('roles')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Filter users by role
     */
    public function filterUsersByRole(string $roleName, int $page = 1, int $perPage = 15)
    {
        return User::whereHas('roles', function ($query) use ($roleName) {
            $query->where('name', $roleName)
                ->orWhere('slug', $roleName);
        })
            ->with('roles')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Log admin action
     */
    private function logAdminAction(User $targetUser, string $action): void
    {
        ActivityLog::create([
            'user_id' => auth()->id() ?? null,
            'action' => $action,
        ]);
    }

    /**
     * Get user permission matrix for display
     */
    public function getUserPermissionMatrix(User $user): array
    {
        $permissions = [];

        foreach ($user->roles as $role) {
            foreach ($role->permissions as $permission) {
                if (!isset($permissions[$permission->category])) {
                    $permissions[$permission->category] = [];
                }

                $permissions[$permission->category][] = [
                    'slug' => $permission->slug,
                    'name' => $permission->name,
                    'granted_through' => 'role: ' . $role->name,
                ];
            }
        }

        // Add direct permissions
        foreach ($user->permissions as $permission) {
            if (!isset($permissions[$permission->category])) {
                $permissions[$permission->category] = [];
            }

            $permissions[$permission->category][] = [
                'slug' => $permission->slug,
                'name' => $permission->name,
                'granted_through' => 'direct assignment',
            ];
        }

        return $permissions;
    }
}
