<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register route middleware (aliases)
        $middleware->alias([
            // Authentication
            'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
            'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,

            // Authorization
            'role' => \App\Middleware\RoleMiddleware::class,
            'permission' => \App\Middleware\PermissionMiddleware::class,

            // Security (if they exist)
            // 'security.check' => \App\Middleware\SecurityCheck::class,
            // 'rate.limit' => \App\Middleware\RateLimiting::class,
            // 'security.headers' => \App\Middleware\SecurityHeaders::class,
            // 'sanitize.input' => \App\Middleware\SanitizeInput::class,
            // 'check.resource.ownership' => \App\Middleware\CheckResourceOwnership::class,

            // Admin check
            'admin' => \Illuminate\Auth\Middleware\AdminCheck::class,
        ]);

        // Global middleware (applied to all requests)
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
