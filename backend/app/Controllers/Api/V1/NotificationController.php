<?php

namespace App\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Notification Controller
 * 
 * Handles notification-related API requests.
 * Provides endpoints for viewing, marking as read, and deleting notifications.
 */
class NotificationController
{
    /**
     * Get user notifications
     * 
     * GET /api/v1/notifications
     */
    public function getNotifications(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $perPage = $request->query('per_page', 10);
            $isRead = $request->query('is_read', null);

            // Validate per_page
            $perPage = min($perPage, 100);

            $query = $user->notifications();

            // Filter by read status if provided
            if ($isRead !== null) {
                $query->where('is_read', (bool) $isRead);
            }

            $notifications = $query
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Notifications retrieved successfully',
                'data' => $notifications->items(),
                'pagination' => [
                    'current_page' => $notifications->currentPage(),
                    'total_pages' => $notifications->lastPage(),
                    'per_page' => $notifications->perPage(),
                    'total' => $notifications->total(),
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
     * Get single notification
     * 
     * GET /api/v1/notifications/{id}
     */
    public function getNotification(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();
            $notification = $user->notifications()->find($id);

            if (!$notification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Notification retrieved successfully',
                'data' => $notification,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark notification as read
     * 
     * PUT /api/v1/notifications/{id}/read
     */
    public function markAsRead(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();
            $notification = $user->notifications()->find($id);

            if (!$notification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification not found',
                ], 404);
            }

            $notification->markAsRead();

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read',
                'data' => $notification,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete notification
     * 
     * DELETE /api/v1/notifications/{id}
     */
    public function deleteNotification(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();
            $notification = $user->notifications()->find($id);

            if (!$notification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification not found',
                ], 404);
            }

            $notification->delete();

            return response()->json([
                'success' => true,
                'message' => 'Notification deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
