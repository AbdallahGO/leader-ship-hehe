<?php

use Illuminate\Support\Facades\Route;
use App\Controllers\Api\V1\AuthController;
use App\Controllers\Api\V1\DashboardController;
use App\Controllers\Api\V1\ProfileController;
use App\Controllers\Api\V1\NotificationController;
use App\Controllers\Api\V1\AdminController;

/**
 * API Routes (v1)
 *
 * All routes in this file are prefixed with /api/v1
 * All routes use JSON responses
 * All routes apply security middleware
 */

// Global security middleware applied to all routes
Route::middleware([
    'security.check',      // Detect suspicious activities
    'rate.limit',          // Rate limiting
    'security.headers',    // Add security headers
    'sanitize.input',      // Sanitize user input
])->group(function () {

    // Authentication routes (public)
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');
        Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.reset');
    });

    // Protected routes (authenticated users only)
    Route::middleware('auth:sanctum')->group(function () {
        // Auth routes
        Route::prefix('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/change-password', [AuthController::class, 'changePassword']);
        });

        // Dashboard routes
        Route::prefix('dashboard')->group(function () {
            Route::get('/', [DashboardController::class, 'getDashboard']);
            Route::get('/statistics', [DashboardController::class, 'getStatistics']);
            Route::get('/activities', [DashboardController::class, 'getActivities']);
        });

        // Profile routes (with resource ownership check)
        Route::prefix('profile')->middleware('check.resource.ownership')->group(function () {
            Route::get('/', [ProfileController::class, 'getProfile']);
            Route::put('/', [ProfileController::class, 'updateProfile']);
            Route::post('/avatar', [ProfileController::class, 'uploadAvatar']);
            Route::delete('/avatar', [ProfileController::class, 'deleteAvatar']);
        });

        // Notification routes (with resource ownership check)
        Route::prefix('notifications')->middleware('check.resource.ownership')->group(function () {
            Route::get('/', [NotificationController::class, 'getNotifications']);
            Route::get('/{id}', [NotificationController::class, 'getNotification']);
            Route::put('/{id}/read', [NotificationController::class, 'markAsRead']);
            Route::delete('/{id}', [NotificationController::class, 'deleteNotification']);
        });
    });

    // Admin routes (requires admin role)
    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::prefix('admin')->group(function () {
            // User Management Endpoints
            Route::get('/users', [AdminController::class, 'listUsers']);
            Route::get('/users/search', [AdminController::class, 'searchUsers']);
            Route::get('/users/{id}', [AdminController::class, 'getUser']);
            Route::put('/users/{id}', [AdminController::class, 'updateUser']);
            Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);
            Route::post('/users/{id}/roles', [AdminController::class, 'assignRole']);
            Route::post('/users/{id}/ban', [AdminController::class, 'banUser']);
            Route::post('/users/{id}/unban', [AdminController::class, 'unbanUser']);
            Route::post('/users/{id}/suspend', [AdminController::class, 'suspendUser']);
            Route::post('/users/{id}/unsuspend', [AdminController::class, 'unsuspendUser']);
            Route::get('/users/{id}/activities', [AdminController::class, 'getUserActivities']);

            // Role Management Endpoints
            Route::get('/roles', [AdminController::class, 'listRoles']);

            // Statistics
            Route::get('/statistics', [AdminController::class, 'getStatistics']);
        });
    });
});
