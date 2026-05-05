<?php

namespace App\Controllers\Api\V1;

use App\Services\UserManagementService;
use App\Services\RoleService;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Admin Controller
 * 
 * Handles admin-related API requests for user and role management.
 * Provides endpoints for user administration, role management, and system statistics.
 * 
 * All endpoints require admin role and authentication.
 */
class AdminController
{
    public function __construct(
        protected UserManagementService $userManagementService,
        protected RoleService $roleService
    ) {}

    /**
     * Get all users with pagination
     * 
     * GET /api/v1/admin/users
     * Query Parameters:
     *   - page: int (default: 1)
     *   - per_page: int (default: 15, max: 100)
     *   - role: string (optional, filter by role)
     *   - status: string (optional: active, banned, suspended)
     */
    public function listUsers(Request $request): JsonResponse
    {
        try {
            $page = $request->query('page', 1);
            $perPage = min($request->query('per_page', 15), 100);
            $status = $request->query('status', null);

            $query = User::with('roles', 'permissions');

            // Filter by status
            if ($status === 'banned') {
                $query->where('is_banned', true);
            } elseif ($status === 'suspended') {
                $query->where('is_suspended', true);
            } elseif ($status === 'active') {
                $query->where('is_banned', false)
                    ->where('is_suspended', false);
            }

            $users = $query->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'message' => 'Users retrieved successfully',
                'data' => $users->items(),
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                    'last_page' => $users->lastPage(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve users',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Search users by name or email
     * 
     * GET /api/v1/admin/users/search
     * Query Parameters:
     *   - q: string (search query)
     *   - page: int (default: 1)
     *   - per_page: int (default: 15)
     */
    public function searchUsers(Request $request): JsonResponse
    {
        try {
            $query = $request->query('q', '');

            if (strlen($query) < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Search query must be at least 2 characters',
                ], 400);
            }

            $page = $request->query('page', 1);
            $perPage = min($request->query('per_page', 15), 100);

            $results = $this->userManagementService->searchUsers($query, $page, $perPage);

            return response()->json([
                'success' => true,
                'message' => 'Users found',
                'data' => $results->items(),
                'pagination' => [
                    'current_page' => $results->currentPage(),
                    'per_page' => $results->perPage(),
                    'total' => $results->total(),
                    'last_page' => $results->lastPage(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user details with all information
     * 
     * GET /api/v1/admin/users/{id}
     */
    public function getUser(int $id): JsonResponse
    {
        try {
            $user = $this->userManagementService->getUserById($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                ], 404);
            }

            $permissionMatrix = $this->userManagementService->getUserPermissionMatrix($user);

            return response()->json([
                'success' => true,
                'message' => 'User retrieved successfully',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                    'is_active' => $user->isActive(),
                    'is_banned' => $user->is_banned,
                    'is_suspended' => $user->is_suspended,
                    'banned_at' => $user->banned_at,
                    'suspended_at' => $user->suspended_at,
                    'roles' => $user->roles->map(fn($role) => [
                        'id' => $role->id,
                        'name' => $role->name,
                        'slug' => $role->slug,
                    ]),
                    'permissions' => $permissionMatrix,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update user information
     * 
     * PUT /api/v1/admin/users/{id}
     * Request Body:
     *   - name: string (optional)
     *   - email: string (optional)
     */
    public function updateUser(Request $request, int $id): JsonResponse
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                ], 404);
            }

            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:users,email,' . $id,
            ]);

            $user = $this->userManagementService->updateUser($user, $validated);

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'data' => $user,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Assign role to user
     * 
     * POST /api/v1/admin/users/{id}/roles
     * Request Body:
     *   - role_id: int (the role to assign)
     */
    public function assignRole(Request $request, int $id): JsonResponse
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                ], 404);
            }

            $validated = $request->validate([
                'role_id' => 'required|integer|exists:roles,id',
            ]);

            $user = $this->userManagementService->updateUserRole($user, $validated['role_id']);

            return response()->json([
                'success' => true,
                'message' => 'Role assigned successfully',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'roles' => $user->roles->map(fn($role) => [
                        'id' => $role->id,
                        'name' => $role->name,
                    ]),
                ],
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign role',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Ban a user
     * 
     * POST /api/v1/admin/users/{id}/ban
     * Request Body:
     *   - reason: string (optional, reason for ban)
     */
    public function banUser(Request $request, int $id): JsonResponse
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                ], 404);
            }

            // Prevent banning self
            if (auth()->id() === $id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot ban yourself',
                ], 400);
            }

            $reason = $request->input('reason', null);
            $user = $this->userManagementService->banUser($user, $reason);

            return response()->json([
                'success' => true,
                'message' => 'User banned successfully',
                'data' => [
                    'id' => $user->id,
                    'is_banned' => $user->is_banned,
                    'banned_at' => $user->banned_at,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to ban user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Unban a user
     * 
     * POST /api/v1/admin/users/{id}/unban
     */
    public function unbanUser(int $id): JsonResponse
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                ], 404);
            }

            if (!$user->is_banned) {
                return response()->json([
                    'success' => false,
                    'message' => 'User is not banned',
                ], 400);
            }

            $user = $this->userManagementService->unbanUser($user);

            return response()->json([
                'success' => true,
                'message' => 'User unbanned successfully',
                'data' => [
                    'id' => $user->id,
                    'is_banned' => $user->is_banned,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to unban user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Suspend a user
     * 
     * POST /api/v1/admin/users/{id}/suspend
     * Request Body:
     *   - reason: string (optional, reason for suspension)
     */
    public function suspendUser(Request $request, int $id): JsonResponse
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                ], 404);
            }

            if (auth()->id() === $id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot suspend yourself',
                ], 400);
            }

            $reason = $request->input('reason', null);
            $user = $this->userManagementService->suspendUser($user, $reason);

            return response()->json([
                'success' => true,
                'message' => 'User suspended successfully',
                'data' => [
                    'id' => $user->id,
                    'is_suspended' => $user->is_suspended,
                    'suspended_at' => $user->suspended_at,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to suspend user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Unsuspend a user
     * 
     * POST /api/v1/admin/users/{id}/unsuspend
     */
    public function unsuspendUser(int $id): JsonResponse
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                ], 404);
            }

            if (!$user->is_suspended) {
                return response()->json([
                    'success' => false,
                    'message' => 'User is not suspended',
                ], 400);
            }

            $user = $this->userManagementService->unsuspendUser($user);

            return response()->json([
                'success' => true,
                'message' => 'User unsuspended successfully',
                'data' => [
                    'id' => $user->id,
                    'is_suspended' => $user->is_suspended,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to unsuspend user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a user
     * 
     * DELETE /api/v1/admin/users/{id}
     */
    public function deleteUser(int $id): JsonResponse
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                ], 404);
            }

            // Prevent deleting self
            if (auth()->id() === $id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete yourself',
                ], 400);
            }

            $this->userManagementService->deleteUser($user);

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get admin statistics
     * 
     * GET /api/v1/admin/statistics
     */
    public function getStatistics(): JsonResponse
    {
        try {
            $stats = $this->userManagementService->getUserStatistics();

            return response()->json([
                'success' => true,
                'message' => 'Statistics retrieved successfully',
                'data' => $stats,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statistics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all roles
     * 
     * GET /api/v1/admin/roles
     */
    public function listRoles(): JsonResponse
    {
        try {
            $roles = $this->roleService->getAll();

            return response()->json([
                'success' => true,
                'message' => 'Roles retrieved successfully',
                'data' => $roles->map(fn($role) => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'slug' => $role->slug,
                    'description' => $role->description,
                    'is_active' => $role->is_active,
                    'permission_count' => $role->permissions()->count(),
                    'user_count' => $role->users()->count(),
                ]),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve roles',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user activity logs
     * 
     * GET /api/v1/admin/users/{id}/activities
     */
    public function getUserActivities(int $id): JsonResponse
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                ], 404);
            }

            $activities = $this->userManagementService->getUserActivityLogs($user, 50);

            return response()->json([
                'success' => true,
                'message' => 'Activities retrieved successfully',
                'data' => $activities,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve activities',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
