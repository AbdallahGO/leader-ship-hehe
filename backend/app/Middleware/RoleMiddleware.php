<?php

namespace App\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Role Middleware
 *
 * Verifies that the authenticated user has one of the required roles.
 * Usage: Route::middleware('role:admin')->...
 *        Route::middleware('role:admin,moderator')->...
 */
class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $user = auth()->user();

        // Check if user has any of the required roles
        if ($user->hasAnyRole($roles)) {
            return $next($request);
        }

        return response()->json([
            'success' => false,
            'message' => 'Insufficient permissions for this action',
        ], 403);
    }
}
