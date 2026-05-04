<?php

namespace App\Controllers\Api\V1;

use App\Repositories\UserRepository;
use App\Services\UploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Profile Controller
 *
 * Handles user profile-related API requests.
 * Provides endpoints for viewing and updating user profiles.
 */
class ProfileController
{
    public function __construct(
        protected UserRepository $userRepository,
        protected UploadService $uploadService
    ) {
    }

    /**
     * Get user profile
     * 
     * GET /api/v1/profile
     */
    public function getProfile(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            return response()->json([
                'success' => true,
                'message' => 'Profile retrieved successfully',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                    'role' => $user->role,
                    'email_verified_at' => $user->email_verified_at,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
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
     * Update user profile
     * 
     * PUT /api/v1/profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:users,email,' . $user->id,
            ]);

            $this->userRepository->update($user, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                    'role' => $user->role,
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
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload user avatar
     *
     * POST /api/v1/profile/avatar
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $request->validate([
                'avatar' => 'required|file|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            ]);

            $file = $request->file('avatar');

            // Upload avatar using service
            $result = $this->uploadService->uploadAvatar($file, $user, [
                'generate_variants' => true,
            ]);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'errors' => $result['errors'] ?? [],
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result['data'],
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
     * Delete user avatar
     *
     * DELETE /api/v1/profile/avatar
     */
    public function deleteAvatar(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user->avatar) {
                return response()->json([
                    'success' => false,
                    'message' => 'User does not have an avatar',
                ], 404);
            }

            // Delete avatar using service
            if (!$this->uploadService->deleteAvatar($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete avatar',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Avatar deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
