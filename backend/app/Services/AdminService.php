<?php

namespace App\Services;

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

/**
 * Admin Service
 *
 * Handles admin operations including user management, banning, and suspension
 */
class AdminService
{
    private RBACService $rbacService;
    private ActivityLoggingService $activityLogger;

    public function __construct(
        RBACService $rbacService,
        ActivityLoggingService $activityLogger
    ) {
        $this->rbacService = $rbacService;
        $this->activityLogger = $activityLogger;
    }

    /**
     * Get all users with pagination
     */
    public function getAllUsers(int $perPage = 50, int $page = 1)
    {
        return User::with('roles')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get user details
     */
    public function getUserDetails(int $userId): ?User
    {
        return User::with(['roles', 'directPermissions'])->find($userId);
    }

    /**
     * Update user
     */
    public function updateUser(User $user, array $data, ?User $admin = null): User
    {
        $changes = [];

        // Track changes for logging
        foreach ($data as $key => $value) {
            if ($key !== 'password' && $user->$key !== $value) {
                $changes[$key] = [
                    'old' => $user->$key,
                    'new' => $value,
                ];
            }
        }

        // Update basic fields
        if (isset($data['name'])) {
            $user->name = $data['name'];
        }
        if (isset($data['email'])) {
            $user->email = $data['email'];
        }

        // Update password if provided
        if (isset($data['password']) && !empty($data['password'])) {
            $user->password = Hash::make($data['password']);
            $changes['password'] = ['old' => '***', 'new' => '***'];
        }

        $user->save();

        // Log activity
        $this->activityLogger->log('admin:user_update', json_encode($changes), $admin?->id);

        return $user;
    }

    /**
     * Ban user
     */
    public function banUser(User $user, string $reason = '', ?User $admin = null): User
    {
        $user->update([
            'is_banned' => true,
            'banned_reason' => $reason,
            'banned_at' => now(),
        ]);

        $this->activityLogger->logSuspiciousActivity('user:banned', json_encode([
            'user_id' => $user->id,
            'reason' => $reason,
            'banned_by' => $admin?->id,
        ]));

        return $user;
    }

    /**
     * Unban user
     */
    public function unbanUser(User $user, ?User $admin = null): User
    {
        $user->update([
            'is_banned' => false,
            'banned_reason' => null,
            'banned_at' => null,
        ]);

        $this->activityLogger->log('admin:user_unbanned', json_encode([
            'user_id' => $user->id,
            'unbanned_by' => $admin?->id,
        ]));

        return $user;
    }

    /**
     * Suspend user
     */
    public function suspendUser(User $user, int $days = 7, string $reason = '', ?User $admin = null): User
    {
        $suspendedUntil = now()->addDays($days);

        $user->update([
            'is_suspended' => true,
            'suspended_until' => $suspendedUntil,
            'suspend_reason' => $reason,
            'suspended_at' => now(),
        ]);

        $this->activityLogger->logSuspiciousActivity('user:suspended', json_encode([
            'user_id' => $user->id,
            'days' => $days,
            'reason' => $reason,
            'suspended_by' => $admin?->id,
        ]));

        return $user;
    }

    /**
     * Unsuspend user
     */
    public function unsuspendUser(User $user, ?User $admin = null): User
    {
        $user->update([
            'is_suspended' => false,
            'suspended_until' => null,
            'suspend_reason' => null,
            'suspended_at' => null,
        ]);

        $this->activityLogger->log('admin:user_unsuspended', json_encode([
            'user_id' => $user->id,
            'unsuspended_by' => $admin?->id,
        ]));

        return $user;
    }

    /**
     * Delete user
     */
    public function deleteUser(User $user, ?User $admin = null): bool
    {
        $this->activityLogger->log('admin:user_deleted', json_encode([
            'user_id' => $user->id,
            'email' => $user->email,
            'deleted_by' => $admin?->id,
        ]));

        return $user->delete();
    }

    /**
     * Restore deleted user
     */
    public function restoreUser(int $userId): bool
    {
        // If using soft deletes
        return User::whereId($userId)->restore() > 0;
    }

    /**
     * Assign role to user
     */
    public function assignRoleToUser(User $user, Role $role, ?User $admin = null): void
    {
        $this->rbacService->assignRoleToUser($user, $role);

        $this->activityLogger->log('admin:role_assigned', json_encode([
            'user_id' => $user->id,
            'role' => $role->slug,
            'assigned_by' => $admin?->id,
        ]));
    }

    /**
     * Remove role from user
     */
    public function removeRoleFromUser(User $user, Role $role, ?User $admin = null): void
    {
        $this->rbacService->removeRoleFromUser($user, $role);

        $this->activityLogger->log('admin:role_removed', json_encode([
            'user_id' => $user->id,
            'role' => $role->slug,
            'removed_by' => $admin?->id,
        ]));
    }

    /**
     * Get user statistics
     */
    public function getUserStatistics(): array
    {
        return [
            'total_users' => User::count(),
            'active_users' => User::where('is_banned', false)->where('is_suspended', false)->count(),
            'banned_users' => User::where('is_banned', true)->count(),
            'suspended_users' => User::where('is_suspended', true)->count(),
            'users_by_role' => User::with('roles')
                ->get()
                ->groupBy(function ($user) {
                    return $user->roles()->first()?->slug ?? 'no_role';
                })
                ->map(fn ($users) => $users->count())
                ->toArray(),
        ];
    }

    /**
     * Search users
     */
    public function searchUsers(string $query, int $perPage = 50)
    {
        return User::where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->with('roles')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get recent activity
     */
    public function getRecentActivity(int $limit = 100): array
    {
        return $this->activityLogger->getSuspiciousActivities($limit);
    }

    /**
     * Export users to CSV
     */
    public function exportUsersToCSV(): array
    {
        return User::with('roles')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => implode(', ', $user->roles()->pluck('name')->toArray()),
                    'is_banned' => $user->is_banned ? 'Yes' : 'No',
                    'is_suspended' => $user->is_suspended ? 'Yes' : 'No',
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ];
            })
            ->toArray();
    }

    /**
     * Check if user can be deleted
     */
    public function canDeleteUser(User $user): bool
    {
        // Prevent deleting if user is admin
        if ($this->rbacService->userHasRole($user, 'admin')) {
            return false;
        }

        return true;
    }

    /**
     * Get inactive users
     */
    public function getInactiveUsers(int $days = 30, int $limit = 100)
    {
        return User::where('last_activity_at', '<', now()->subDays($days))
            ->orderBy('last_activity_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
