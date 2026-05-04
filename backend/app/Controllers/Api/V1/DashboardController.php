<?php

namespace App\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Dashboard Controller
 * 
 * Handles dashboard-related API requests.
 * Provides endpoints for dashboard data, statistics, and activities.
 */
class DashboardController
{
    /**
     * Get dashboard data
     * 
     * GET /api/v1/dashboard
     */
    public function getDashboard(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Get dashboard data
            $dashboardData = [
                'welcome' => "Welcome, {$user->name}!",
                'unread_notifications' => $user->notifications()->where('is_read', false)->count(),
                'total_activities' => $user->activityLogs()->count(),
                'active_sessions' => $user->sessions()->count(),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Dashboard data retrieved successfully',
                'data' => $dashboardData,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get statistics
     * 
     * GET /api/v1/dashboard/statistics
     */
    public function getStatistics(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $statistics = [
                'total_notifications' => $user->notifications()->count(),
                'unread_notifications' => $user->notifications()->where('is_read', false)->count(),
                'total_activities' => $user->activityLogs()->count(),
                'active_sessions' => $user->sessions()->count(),
                'account_age_days' => $user->created_at->diffInDays(now()),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Statistics retrieved successfully',
                'data' => $statistics,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get recent activities
     * 
     * GET /api/v1/dashboard/activities
     */
    public function getActivities(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $perPage = $request->query('per_page', 10);

            // Validate per_page
            $perPage = min($perPage, 100);

            $activities = $user->activityLogs()
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Activities retrieved successfully',
                'data' => $activities->items(),
                'pagination' => [
                    'current_page' => $activities->currentPage(),
                    'total_pages' => $activities->lastPage(),
                    'per_page' => $activities->perPage(),
                    'total' => $activities->total(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
