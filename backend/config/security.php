<?php

/**
 * Security Configuration
 *
 * This file contains all security-related configurations for the application
 */

return [
    /**
     * HTTPS/SSL Configuration
     */
    'https' => [
        // Force HTTPS in production
        'force_https' => env('APP_ENV') === 'production',

        // HSTS (Strict-Transport-Security) max age in seconds (1 year)
        'hsts_max_age' => 31536000,

        // Include subdomains in HSTS
        'hsts_include_subdomains' => true,

        // Preload HSTS (optional, but recommended)
        'hsts_preload' => true,
    ],

    /**
     * CORS Configuration
     */
    'cors' => [
        'allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', 'http://localhost:3000')),
        'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'PATCH'],
        'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],
        'expose_headers' => ['Content-Length', 'X-JSON-Response-Time'],
        'max_age' => 86400,
        'supports_credentials' => true,
    ],

    /**
     * Rate Limiting
     */
    'rate_limit' => [
        // Requests per minute per IP
        'api_requests_per_minute' => (int)env('RATE_LIMIT_API', 60),

        // Login attempts per 15 minutes
        'login_attempts' => 5,
        'login_lockout_duration' => 15, // minutes

        // Password reset attempts per hour
        'password_reset_attempts' => 3,
        'password_reset_window' => 60, // minutes
    ],

    /**
     * Password Security
     */
    'password' => [
        // Minimum password length
        'min_length' => 8,

        // Require uppercase letters
        'require_uppercase' => true,

        // Require lowercase letters
        'require_lowercase' => true,

        // Require numbers
        'require_numbers' => true,

        // Require special characters
        'require_special_chars' => true,

        // Password expiration in days (0 = disabled)
        'expiration_days' => 0,

        // Number of previous passwords to check against
        'history_count' => 3,
    ],

    /**
     * Session Security
     */
    'session' => [
        // Session timeout in minutes
        'timeout' => 30,

        // Secure cookie (HTTPS only)
        'secure_cookie' => env('APP_ENV') === 'production',

        // HttpOnly cookie (no JavaScript access)
        'http_only' => true,

        // Same site cookie setting
        'same_site' => 'strict',
    ],

    /**
     * API Security
     */
    'api' => [
        // Require authentication for all API endpoints
        'require_auth' => true,

        // API version
        'version' => 'v1',

        // Token expiration in hours
        'token_expiration' => 24,

        // Enable request signing (additional security)
        'enable_request_signing' => env('ENABLE_REQUEST_SIGNING', false),
    ],

    /**
     * File Upload Security
     */
    'uploads' => [
        // Maximum file size in KB
        'max_size' => 5120, // 5MB

        // Allowed MIME types
        'allowed_mimes' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
        ],

        // Allowed extensions
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],

        // Scan uploaded files (requires external service)
        'scan_files' => false,
    ],

    /**
     * Security Headers
     */
    'headers' => [
        // Content Security Policy
        'csp' => "default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:",

        // X-Frame-Options
        'x_frame_options' => 'DENY',

        // X-Content-Type-Options
        'x_content_type_options' => 'nosniff',

        // X-XSS-Protection
        'x_xss_protection' => '1; mode=block',

        // Referrer-Policy
        'referrer_policy' => 'strict-origin-when-cross-origin',

        // Permissions-Policy (formerly Feature-Policy)
        'permissions_policy' => 'geolocation=(), microphone=(), camera=(), payment=()',
    ],

    /**
     * Activity Logging
     */
    'logging' => [
        // Log all user activities
        'log_activities' => true,

        // Log API requests
        'log_api_requests' => env('LOG_API_REQUESTS', false),

        // Log authentication events
        'log_auth_events' => true,

        // Log suspicious activities
        'log_suspicious_activity' => true,

        // Retention period in days
        'retention_days' => 90,
    ],

    /**
     * Two-Factor Authentication
     */
    'two_factor' => [
        // Enable 2FA
        'enabled' => false,

        // Default provider (totp, email, sms)
        'provider' => 'totp',

        // OTP validity in seconds
        'otp_validity' => 300,
    ],

    /**
     * Encryption
     */
    'encryption' => [
        // Encrypt sensitive user data
        'enabled' => true,

        // Algorithm (AES-256-CBC)
        'algorithm' => 'AES-256-CBC',

        // Fields to encrypt
        'encrypted_fields' => [
            // Add sensitive fields here
        ],
    ],
];
