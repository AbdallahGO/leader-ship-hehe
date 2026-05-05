<?php

namespace App\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Permission Middleware
 *
 * Verifies that the authenticated user has one of the required permissions.
 * Usage: Route::middleware('permission:users.read')->...
 *        Route::middleware('permission:users.read,users.create')->...
 */
class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $user = auth()->user();

        // Check if user has any of the required permissions
        if ($user->hasAnyPermission($permissions)) {
            return $next($request);
        }

        return response()->json([
            'success' => false,
            'message' => 'You do not have permission to perform this action',
        ], 403);
    }
}
