<?php

namespace App\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Security Headers Middleware
 * 
 * Adds security-related HTTP headers to all responses
 * Protects against common web vulnerabilities
 */
class SecurityHeaders
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $response = $next($request);

        // Prevent clickjacking attacks - don't allow embedding in frames
        $response->header('X-Frame-Options', 'DENY');

        // Prevent MIME sniffing - force browser to respect Content-Type
        $response->header('X-Content-Type-Options', 'nosniff');

        // Enable XSS protection in older browsers
        $response->header('X-XSS-Protection', '1; mode=block');

        // Referrer Policy - control how much referrer info is sent
        $response->header('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Content Security Policy - prevent inline script execution
        $response->header('Content-Security-Policy', "default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'");

        // Permissions Policy - control browser features
        $response->header('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        // HSTS - enforce HTTPS (only in production)
        if (config('app.env') === 'production') {
            $response->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // Remove server identification
        $response->header('Server', 'PHP');

        return $response;
    }
}
