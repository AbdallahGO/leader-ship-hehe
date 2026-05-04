<?php

namespace App\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Input Sanitization Middleware
 * 
 * Sanitizes user input to prevent XSS and injection attacks
 */
class SanitizeInput
{
    /**
     * Fields that should not be sanitized
     */
    protected array $except = [
        'password',
        'password_confirmation',
        'token',
        'remember_token',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        // Sanitize input only for POST, PUT, PATCH requests
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH'])) {
            $input = $request->all();
            
            foreach ($input as $key => $value) {
                if ($this->shouldSanitize($key)) {
                    $input[$key] = $this->sanitize($value);
                }
            }
            
            $request->merge($input);
        }

        return $next($request);
    }

    /**
     * Determine if the input should be sanitized.
     */
    protected function shouldSanitize(string $key): bool
    {
        return !in_array($key, $this->except);
    }

    /**
     * Sanitize input value.
     */
    protected function sanitize(mixed $value): mixed
    {
        if (is_string($value)) {
            // Remove null bytes
            $value = str_replace("\0", "", $value);
            
            // HTML encode special characters
            $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            
            // Trim whitespace
            $value = trim($value);
        } elseif (is_array($value)) {
            foreach ($value as $key => $item) {
                $value[$key] = $this->sanitize($item);
            }
        }

        return $value;
    }
}
