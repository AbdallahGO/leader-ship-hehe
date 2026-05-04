<?php

namespace App\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Authentication Middleware
 * 
 * Ensures user is authenticated before accessing protected routes.
 */
class Authenticate
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        return $next($request);
    }
}
