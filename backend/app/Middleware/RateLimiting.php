<?php

namespace App\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiter;

/**
 * Rate Limiting Middleware
 * 
 * Implements API rate limiting to prevent abuse.
 * Default: 60 requests per minute per IP address
 */
class RateLimiting
{
    /**
     * The rate limiter instance.
     */
    protected RateLimiter $limiter;

    /**
     * Create a new middleware instance.
     */
    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $key = $this->resolveRequestSignature($request);
        
        // 60 requests per minute per IP
        if ($this->limiter->tooManyAttempts($key, 60)) {
            return response()->json([
                'success' => false,
                'message' => 'Too many requests. Please try again later.',
            ], 429);
        }

        $this->limiter->hit($key);

        return $next($request);
    }

    /**
     * Resolve request signature for rate limiting.
     */
    protected function resolveRequestSignature(Request $request): string
    {
        return sha1(
            $request->method() .
            '|' . $request->getHost() .
            '|' . $request->ip()
        );
    }
}
