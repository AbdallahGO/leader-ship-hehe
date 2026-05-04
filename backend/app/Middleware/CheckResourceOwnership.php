<?php

namespace App\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Authorization Middleware
 *
 * Ensures users can only access/modify their own resources
 * unless they are admins
 */
class CheckResourceOwnership
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        // Admin users bypass ownership checks
        if (auth()->check() && auth()->user()->role === 'admin') {
            return $next($request);
        }

        // Check if the request is for a specific user resource
        if ($request->route('id')) {
            $resourceId = $request->route('id');
            $authenticatedUserId = auth()->id();

            // Prevent users from accessing other users' resources
            if ((int)$resourceId !== $authenticatedUserId && !is_admin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You can only access your own resources.',
                ], 403);
            }
        }

        return $next($request);
    }
}

/**
 * Helper function to check if user is admin
 */
if (!function_exists('is_admin')) {
    function is_admin(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }
}
