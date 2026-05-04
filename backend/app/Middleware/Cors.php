<?php

namespace App\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * CORS Middleware
 * 
 * Handles Cross-Origin Resource Sharing (CORS) configuration
 * Allows frontend to communicate with backend API
 */
class Cors
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $allowedOrigins = array_filter(
            explode(',', config('app.cors_origins', 'http://localhost:3000'))
        );

        $origin = $request->header('Origin');

        // Check if origin is allowed
        $isAllowedOrigin = empty($allowedOrigins) || in_array($origin, $allowedOrigins);

        if ($isAllowedOrigin) {
            $response = $next($request);

            $response->header('Access-Control-Allow-Origin', $origin);
            $response->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS, PATCH');
            $response->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
            $response->header('Access-Control-Allow-Credentials', 'true');
            $response->header('Access-Control-Max-Age', '86400');

            return $response;
        }

        // Handle preflight requests
        if ($request->method() === 'OPTIONS') {
            return response('', 204)
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS, PATCH')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        }

        return $next($request);
    }
}
