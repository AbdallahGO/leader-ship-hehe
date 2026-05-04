<?php

namespace App\Controllers\Api\V1;

use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Admin Controller
 * 
 * Handles admin-related API requests.
 * Provides endpoints for user management and system administration.
 */
class AdminController
{
    public function __construct(protected UserRepository $userRepository)
    {
    }

    /**
     * Get all users (admin only)
     * 
     * GET /api/v1/admin/users
     */
    public function getUsers(Request $request): JsonResponse
    {
        try {
            $perPage = $request->query('per_page', 15);
            $role = $request->query('role', null);

            // Validate per_page
            $perPage = min($perPage, 100);

            $query = \App\Models\User::query();

            // Filter by role if provided
            if ($role) {
                $query->where('role', $role);
            }

            $users = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Users retrieved successfully',
                'data' => $users->items(),
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'total_pages' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user details (admin only)
     * 
     * GET /api/v1/admin/users/{id}
     */
    public function getUser(Request $request, int $id): JsonResponse
    {
        try {
            $user = $this->userRepository->findById($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'User retrieved successfully',
                'data' => $user,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update user (admin only)
     * 
     * PUT /api/v1/admin/users/{id}
     */
    public function updateUser(Request $request, int $id): JsonResponse
    {
        try {
            $user = $this->userRepository->findById($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                ], 404);
            }

            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:users,email,' . $id,
                'role' => 'sometimes|in:user,moderator,admin',
            ]);

            $this->userRepository->update($user, $validated);

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'data' => $user->refresh(),
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
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete user (admin only)
     * 
     * DELETE /api/v1/admin/users/{id}
     */
    public function deleteUser(Request $request, int $id): JsonResponse
    {
        try {
            // Prevent admin from deleting themselves
            if ($request->user()->id === $id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete your own account',
                ], 400);
            }

            $user = $this->userRepository->findById($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                ], 404);
            }

            $this->userRepository->delete($user);

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
