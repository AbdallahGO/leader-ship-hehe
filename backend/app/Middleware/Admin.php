<?php

namespace App\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Admin Middleware
 * 
 * Ensures user has admin role before accessing protected routes.
 */
class Admin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $user = $request->user();

        if (!$user || !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden - Admin access required',
            ], 403);
        }

        return $next($request);
    }
}
