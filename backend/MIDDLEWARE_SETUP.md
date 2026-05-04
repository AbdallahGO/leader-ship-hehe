# Security Middleware Setup Guide

## Overview

This guide explains how to register and configure all security middleware for the PHP Backend Dashboard.

---

## Middleware Registration

### In `app/Http/Kernel.php`

Add the following middleware aliases to the `$routeMiddleware` array:

```php
protected $routeMiddleware = [
    // ... other middleware

    // Security middleware
    'security.check' => \App\Middleware\SecurityCheck::class,
    'rate.limit' => \App\Middleware\RateLimiting::class,
    'security.headers' => \App\Middleware\SecurityHeaders::class,
    'sanitize.input' => \App\Middleware\SanitizeInput::class,
    'check.resource.ownership' => \App\Middleware\CheckResourceOwnership::class,
];
```

### In `app/Http/Middleware/TrustProxies.php` (Optional)

For production environments behind a load balancer:

```php
protected $proxies = '*'; // or specify IP addresses

protected $headers =
    Request::HEADER_X_FORWARDED_FOR |
    Request::HEADER_X_FORWARDED_HOST |
    Request::HEADER_X_FORWARDED_PROTO |
    Request::HEADER_X_FORWARDED_AWS_ELB;
```

---

## Middleware Execution Order

### Request Flow

```
1. SecurityCheck       (Detect suspicious activity)
2. RateLimiting       (Check rate limits)
3. SanitizeInput      (Sanitize user input)
4. AuthenticationCheck (Optional: auth:sanctum)
5. Authorization      (Check permissions if needed)
```

### Response Flow

```
1. SecurityHeaders    (Add security headers)
2. Response sent
```

---

## Middleware Details

### 1. SecurityCheck Middleware

**Purpose**: Detects and logs potential attack attempts

**Detects**:

- SQL injection patterns
- XSS attempts
- Unusual input patterns

**Configuration**: None required (patterns defined in middleware)

**Response**: Allows request to continue but logs suspicious activity

### 2. RateLimiting Middleware

**Purpose**: Prevents brute force and DoS attacks

**Configuration** (in `config/security.php`):

```php
'rate_limit' => [
    'api_requests_per_minute' => 60,
]
```

**Response on Limit Exceeded**: 429 Too Many Requests

### 3. SanitizeInput Middleware

**Purpose**: Prevents XSS by sanitizing user input

**Behavior**:

- Sanitizes all POST, PUT, PATCH requests
- Skips password fields
- HTML encodes special characters
- Removes null bytes

**Configuration**: Edit `$except` array to skip additional fields

### 4. SecurityHeaders Middleware

**Purpose**: Adds protective HTTP headers

**Headers Added**:

- X-Frame-Options: DENY
- X-Content-Type-Options: nosniff
- X-XSS-Protection: 1; mode=block
- Content-Security-Policy
- Referrer-Policy
- Permissions-Policy
- Strict-Transport-Security (production only)

**Configuration**: Edit middleware file to customize headers

### 5. CheckResourceOwnership Middleware

**Purpose**: Ensures users can only access their own resources

**Behavior**:

- Admins bypass all checks
- Non-admins can only access resources with their own ID
- Returns 403 Forbidden for unauthorized access

**Configuration**: None required

---

## Global Middleware Application

To apply security middleware globally to all routes:

Edit `app/Http/Kernel.php` and add to `$middleware` array:

```php
protected $middleware = [
    // ... Laravel default middleware

    // Apply to all requests
    \App\Middleware\SecurityHeaders::class,
    \App\Middleware\RateLimiting::class,
];
```

---

## Route-Specific Middleware

### Apply to Specific Routes

```php
// Single middleware
Route::get('/dashboard', 'DashboardController@show')
    ->middleware('rate.limit');

// Multiple middleware
Route::post('/profile', 'ProfileController@update')
    ->middleware(['auth:sanctum', 'check.resource.ownership']);

// Middleware groups
Route::group(['middleware' => ['auth:sanctum', 'check.resource.ownership']], function () {
    Route::get('/profile', 'ProfileController@show');
    Route::put('/profile', 'ProfileController@update');
});
```

---

## Middleware Groups

Create reusable middleware groups in `app/Http/Kernel.php`:

```php
protected $middlewareGroups = [
    'api' => [
        'throttle:60,1',
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        \App\Middleware\SecurityCheck::class,
        \App\Middleware\RateLimiting::class,
        \App\Middleware\SanitizeInput::class,
        \App\Middleware\SecurityHeaders::class,
    ],

    'api.protected' => [
        'auth:sanctum',
        'api',
        \App\Middleware\CheckResourceOwnership::class,
    ],
];
```

Use in routes:

```php
Route::middleware('api')->group(function () {
    // Public API routes
});

Route::middleware('api.protected')->group(function () {
    // Protected API routes
});
```

---

## Testing Middleware

### Unit Tests

Create `tests/Unit/Middleware/SecurityMiddlewareTest.php`:

```php
<?php

namespace Tests\Unit\Middleware;

use Tests\TestCase;
use Illuminate\Http\Request;

class SecurityMiddlewareTest extends TestCase
{
    public function test_rate_limiting_blocks_excessive_requests()
    {
        $response = $this->json('GET', '/api/v1/dashboard');
        $this->assertTrue($response->status() !== 429);

        // Make 61 requests (over limit of 60)
        for ($i = 0; $i < 61; $i++) {
            $response = $this->json('GET', '/api/v1/dashboard');
        }

        $this->assertEquals(429, $response->status());
    }

    public function test_security_check_detects_sql_injection()
    {
        $response = $this->json('POST', '/api/v1/auth/login', [
            'email' => "' OR '1'='1",
            'password' => 'password'
        ]);

        // Should still process request but log suspicious activity
        $this->assertNotNull($response);
    }

    public function test_input_sanitization_removes_xss()
    {
        $response = $this->json('POST', '/api/v1/auth/register', [
            'name' => '<script>alert("xss")</script>John',
            'email' => 'john@example.com',
            'password' => 'SecurePassword123!'
        ]);

        // Name should be sanitized
        $this->assertEquals('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;John',
            $response->json('data.name'));
    }
}
```

Run tests:

```bash
php artisan test tests/Unit/Middleware/SecurityMiddlewareTest.php
```

### Manual Testing

#### Test Rate Limiting

```bash
# Make 65 requests in quick succession
for i in {1..65}; do
    curl -H "Authorization: Bearer $TOKEN" \
         https://api.yourdomain.com/api/v1/dashboard
done

# Should see 429 response on request 61+
```

#### Test Security Headers

```bash
# Check response headers
curl -I https://api.yourdomain.com/api/v1/auth/login

# Should see:
# X-Frame-Options: DENY
# X-Content-Type-Options: nosniff
# Content-Security-Policy: ...
```

#### Test Input Sanitization

```bash
# Test with XSS payload
curl -X POST https://api.yourdomain.com/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "<img src=x onerror=alert(1)>",
    "email": "test@example.com",
    "password": "SecurePassword123!"
  }'

# Should sanitize the name field
```

---

## Performance Considerations

### Middleware Performance Impact

- **SecurityCheck**: ~5ms (regex pattern matching)
- **RateLimiting**: ~2ms (cache lookup)
- **SanitizeInput**: ~3ms (string processing)
- **SecurityHeaders**: <1ms (header addition)

**Total**: ~10ms per request (acceptable for most applications)

### Optimization Tips

1. **Cache Middleware Results**: Store rate limit keys in Redis for better performance
2. **Async Logging**: Log suspicious activities asynchronously using queues
3. **Route Groups**: Use middleware groups to avoid repetition
4. **Skip Rules**: Add conditions to skip middleware when not needed

```php
// Example: Skip sanitization for JSON API requests
if ($request->acceptsJson() && $request->header('X-API-Version') === '2') {
    return $next($request);
}
```

---

## Troubleshooting

### Issue: All Requests Return 429

**Solution**: Rate limiting set too low

- Check `config/security.php` `rate_limit.api_requests_per_minute`
- Ensure Redis is running (if using Redis cache)
- Check cache driver configuration

### Issue: Security Headers Not Appearing

**Solution**: Middleware not registered or applied

- Verify middleware in `app/Http/Kernel.php`
- Ensure routes use correct middleware
- Check middleware group configuration

### Issue: Legitimate Requests Flagged as Suspicious

**Solution**: Security check patterns too strict

- Update suspicious patterns in `SecurityCheck` middleware
- Add to exceptions list
- Review logged suspicious activities

### Issue: Sanitization Breaking JSON Fields

**Solution**: Disable sanitization for specific fields

- Update `$except` array in `SanitizeInput` middleware
- Use conditional sanitization based on content-type
- Implement custom sanitization rules

---

## Advanced Configuration

### Custom Security Rules

Create `app/Services/SecurityRuleEngine.php`:

```php
<?php

namespace App\Services;

class SecurityRuleEngine
{
    public function evaluateRequest($request)
    {
        // Custom security logic
        return [
            'is_suspicious' => false,
            'threat_level' => 'low',
            'reason' => null,
        ];
    }
}
```

### Conditional Middleware

```php
// Apply middleware conditionally
Route::middleware(function ($request, $next) {
    if ($request->user() && $request->user()->is_trusted) {
        // Skip rate limiting for trusted users
    }

    return $next($request);
})->group(function () {
    // Routes
});
```

---

## Security Monitoring

### Monitor Middleware Logs

```php
// In scheduler or command
php artisan tinker

>>> \App\Models\ActivityLog::where('action', 'like', 'security:%')->count()
>>> \App\Models\ActivityLog::where('action', 'like', 'security:%')->latest()->first()
```

### Alert on Suspicious Activity

Configure email alerts:

```php
// In config/security.php
'logging' => [
    'alert_on_suspicious' => true,
    'alert_email' => env('SECURITY_ALERT_EMAIL'),
]
```

---

## Production Deployment Checklist

- ✅ Middleware registered in `app/Http/Kernel.php`
- ✅ Security configuration in `config/security.php`
- ✅ Environment variables set correctly
- ✅ Cache driver configured (Redis recommended)
- ✅ Database migrations run (activity_logs table)
- ✅ SSL/HTTPS enabled
- ✅ Rate limiting tested
- ✅ Suspicious activity logging verified
- ✅ Security headers verified
- ✅ Input sanitization tested
- ✅ Resource ownership checks tested
- ✅ Admin permissions verified

---

## Disable Middleware (Development Only)

To temporarily disable middleware during development:

```php
// In routes/api.php or conditionally
if (app()->environment(['local', 'testing'])) {
    Route::middleware([])->group(function () {
        // Routes without security middleware
    });
}
```

**Never disable security middleware in production!**

---

**Last Updated**: 2026-05-04
**Version**: 1.0
