# Security Implementation Guide

## Phase 5 - Security Layer

This document outlines all security measures implemented in the PHP Backend Dashboard API.

---

## 1. Authentication & Authorization

### Authentication Method
- **Technology**: Laravel Sanctum (token-based API authentication)
- **Token Storage**: Bearer tokens sent in `Authorization` header
- **Token Expiration**: 24 hours (configurable in `config/security.php`)

### Authorization Levels
- **Public Routes**: Registration, Login, Forgot Password, Reset Password
- **Authenticated Routes**: Dashboard, Profile, Notifications (requires valid token)
- **Admin Routes**: User management (requires admin role)

### Implementation
```php
// Protected routes require authentication
Route::middleware('auth:sanctum')->group(function () {
    // Protected endpoints
});

// Admin routes require admin role
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    // Admin endpoints
});
```

---

## 2. Request Validation

All API endpoints validate incoming data to prevent invalid or malicious input.

### Validation Classes
- **RegisterRequest**: Email format, strong password, name format
- **LoginRequest**: Valid email, minimum password length
- **ForgotPasswordRequest**: Valid email address
- **ResetPasswordRequest**: Valid token, strong password
- **ChangePasswordRequest**: Current password verification, strong new password
- **UpdateProfileRequest**: Name format, email uniqueness, phone format
- **UploadAvatarRequest**: Image file validation, size limits, dimensions
- **UpdateUserRequest** (Admin): Role validation, user status
- **MarkNotificationAsReadRequest**: Notification ID validation

### Validation Rules Applied
- Email format validation (RFC compliant)
- Strong password requirements:
  - Minimum 8 characters
  - Must contain uppercase letters
  - Must contain lowercase letters
  - Must contain numbers
  - Must contain special characters (@$!%*?&)
- File type validation for uploads
- Input length limits
- Regex pattern matching for name fields
- Uniqueness checks for email addresses

---

## 3. Input Sanitization

### SanitizeInput Middleware
Protects against XSS attacks by sanitizing user input:

- **HTML Entity Encoding**: Converts special characters to HTML entities
- **Null Byte Removal**: Removes null bytes from input
- **Whitespace Trimming**: Trims leading/trailing whitespace
- **Array Sanitization**: Recursively sanitizes nested arrays

### Protected Fields (Not Sanitized)
- `password`
- `password_confirmation`
- `token`
- `remember_token`

### Implementation
```php
// Automatic sanitization applied to POST, PUT, PATCH requests
if (in_array($request->method(), ['POST', 'PUT', 'PATCH'])) {
    $input = $request->sanitize();
}
```

---

## 4. Security Headers

### SecurityHeaders Middleware
Adds protective HTTP headers to all responses:

| Header | Value | Purpose |
|--------|-------|---------|
| X-Frame-Options | DENY | Prevents clickjacking attacks |
| X-Content-Type-Options | nosniff | Prevents MIME type sniffing |
| X-XSS-Protection | 1; mode=block | Enables browser XSS filters |
| Content-Security-Policy | restricted | Prevents inline script execution |
| Referrer-Policy | strict-origin-when-cross-origin | Controls referrer information |
| Permissions-Policy | geolocation=(), microphone=(), camera=() | Restricts browser features |
| Strict-Transport-Security | max-age=31536000 | Enforces HTTPS (production only) |

---

## 5. Rate Limiting

### RateLimiting Middleware
Protects against brute force and DoS attacks:

- **API Rate Limit**: 60 requests per minute per IP address
- **Response Code**: 429 (Too Many Requests)

### Configuration
```php
// config/security.php
'rate_limit' => [
    'api_requests_per_minute' => 60,
]
```

---

## 6. Login Attempt Protection

### LoginAttemptService
Prevents brute force attacks on login endpoint:

- **Max Failed Attempts**: 5 attempts
- **Lockout Duration**: 15 minutes
- **Per**: Email address (not IP)

### Features
- Tracks failed login attempts
- Automatically locks account after 5 failed attempts
- Returns remaining attempts to client
- Clears attempts on successful login

### Response Example
```json
{
    "success": false,
    "message": "Account locked due to too many failed attempts",
    "remaining_attempts": 0,
    "lockout_duration_minutes": 15
}
```

---

## 7. Security Check Middleware

### Suspicious Activity Detection
Identifies and logs potential attack attempts:

#### SQL Injection Detection
Patterns detected:
- `' OR '` constructs
- `UNION SELECT` statements
- `INSERT INTO` statements
- `DELETE FROM` statements
- `DROP TABLE` statements
- `EXEC()` or `EXECUTE()` calls

#### XSS Detection
Patterns detected:
- `<script>` tags
- `<iframe>` tags
- `javascript:` protocol
- Event handlers (onclick, onload, etc.)
- `<svg>` tags with potential event handlers
- Image tags with event handlers

#### Logging
All detected attempts are logged with:
- Attack type
- Parameter name
- User ID
- IP address
- Request path
- Timestamp

---

## 8. Activity Logging

### ActivityLoggingService
Maintains audit trail of all user actions:

#### Logged Events
- Authentication (login, logout, password reset)
- Profile updates (name, email changes)
- Password changes
- Avatar uploads and deletions
- Admin actions
- Suspicious activities

#### Logged Information
- User ID
- Action performed
- Timestamp
- IP address
- User agent (browser/device info)
- Additional details (JSON encoded)

#### Retrieval
```php
// Get user's recent activity
$activities = $activityLogger->getUserActivity($userId, limit: 50);

// Get suspicious activities for review
$suspicious = $activityLogger->getSuspiciousActivities(limit: 100);
```

---

## 9. Authorization Middleware

### CheckResourceOwnership Middleware
Ensures users can only access their own resources:

- **User Resources**: Can only view/edit own profile
- **Admin Override**: Admin users can access any resource
- **Response Code**: 403 (Forbidden) for unauthorized access

### Implementation
```php
// Prevents non-admin users from accessing other users' data
Route::put('/profile/{id}', ...)->middleware('check.ownership');
```

---

## 10. HTTPS/SSL Configuration

### Production Requirements
- Force HTTPS redirection
- Secure cookies only
- HSTS header enforcement

### Configuration
```php
// config/security.php
'https' => [
    'force_https' => env('APP_ENV') === 'production',
    'hsts_max_age' => 31536000,
    'hsts_include_subdomains' => true,
    'hsts_preload' => true,
]
```

### Setup Steps
1. Obtain SSL certificate (Let's Encrypt recommended)
2. Configure web server (Nginx/Apache)
3. Update `.env`: `APP_URL=https://yourdomain.com`
4. Enable HSTS headers in production

---

## 11. SQL Injection Prevention

### Laravel ORM Protection
- Uses **parameterized queries** automatically
- All database queries use Eloquent ORM
- No raw SQL queries in sensitive areas
- Input validation as additional layer

### Example
```php
// Safe - uses parameterized query
User::where('email', $email)->first();

// Vulnerable - DO NOT USE
DB::select("SELECT * FROM users WHERE email = '$email'");
```

---

## 12. CORS Configuration

### Allowed Origins
- Configured via `CORS_ALLOWED_ORIGINS` environment variable
- Default: `http://localhost:3000`
- Production: Should be restricted to your frontend domain

### Configuration
```bash
# .env
CORS_ALLOWED_ORIGINS=https://yourdomain.com,https://api.yourdomain.com
```

### Allowed Methods
- GET, POST, PUT, DELETE, PATCH, OPTIONS

---

## 13. Password Security

### Requirements
- Minimum 8 characters
- Must include uppercase letters
- Must include lowercase letters
- Must include numbers
- Must include special characters

### Hashing
- Algorithm: bcrypt (Laravel default)
- Cost factor: configurable (default: 12)
- Never stored in plain text

---

## 14. Session Security

### Configuration
- **Timeout**: 30 minutes of inactivity
- **Secure Cookie**: HTTPS only in production
- **HttpOnly**: No JavaScript access to tokens
- **SameSite**: Strict cookie policy

---

## 15. File Upload Security

### Avatar Upload Protection
- **Allowed Types**: JPEG, PNG, GIF, WebP
- **Max Size**: 5MB
- **Dimensions**: 100x100 to 4000x4000 pixels
- **Storage**: Outside public directory
- **Verification**: MIME type validation

### Validation
```php
'avatar' => [
    'required',
    'image',
    'mimes:jpeg,png,gif,webp',
    'max:5120', // 5MB
    'dimensions:min_width=100,min_height=100,max_width=4000,max_height=4000',
]
```

---

## 16. Environment Configuration

### Critical Variables
```bash
APP_ENV=production          # Environment
APP_DEBUG=false            # Disable debug mode
APP_URL=https://domain.com # HTTPS URL
APP_KEY=base64:...         # Generated encryption key

CORS_ALLOWED_ORIGINS=...   # Frontend domain
RATE_LIMIT_API=60          # Requests per minute
LOG_API_REQUESTS=false     # Log all API requests
ENABLE_REQUEST_SIGNING=false # Enable request signing
```

### Never Commit
- `.env` file
- Database credentials
- API keys
- SSL certificates

---

## 17. Security Best Practices

### For Developers
1. ✅ Always validate input on server-side
2. ✅ Use parameterized queries
3. ✅ Sanitize output for the context (HTML, JSON, etc.)
4. ✅ Use HTTPS in production
5. ✅ Keep dependencies updated
6. ✅ Log security events
7. ✅ Use environment variables for secrets
8. ✅ Implement rate limiting
9. ✅ Use strong password hashing
10. ✅ Implement audit logging

### For DevOps
1. ✅ Enable firewall rules
2. ✅ Use strong SSL certificates
3. ✅ Enable security headers in web server
4. ✅ Monitor API logs
5. ✅ Set up automated backups
6. ✅ Use Web Application Firewall (WAF)
7. ✅ Enable DDoS protection
8. ✅ Regular security updates

---

## 18. Testing Security Features

### Unit Tests
```bash
php artisan test --filter=SecurityTest
```

### Manual Testing
1. Test rate limiting (60+ requests/minute)
2. Test login lockout (6 failed attempts)
3. Test input sanitization with XSS payloads
4. Test SQL injection patterns
5. Test unauthorized access (403 responses)
6. Test invalid file uploads
7. Verify security headers in response

### Tools
- **Postman**: API testing
- **OWASP ZAP**: Security scanning
- **Burp Suite**: Web security testing
- **curl**: Manual header testing

```bash
# Test security headers
curl -I https://yourdomain.com/api/v1/health

# Test rate limiting
for i in {1..70}; do curl https://yourdomain.com/api/v1/dashboard; done
```

---

## 19. Monitoring & Alerts

### Log Locations
- Application logs: `storage/logs/`
- Activity logs: Database `activity_logs` table
- Suspicious activities: Query `activity_logs` where `action` LIKE `'security:%'`

### Key Metrics to Monitor
- Failed login attempts
- Rate limit violations
- Suspicious activity detections
- Unauthorized access attempts
- File upload failures

---

## 20. Incident Response

### If Security Breach Detected
1. Immediately disable compromised accounts
2. Review activity logs for affected user IDs
3. Check for unauthorized data access
4. Notify affected users
5. Rotate API keys
6. Audit file uploads
7. Review and update security rules

---

## Compliance Checklist

- ✅ Input validation on all endpoints
- ✅ Output encoding for XSS prevention
- ✅ SQL injection prevention (ORM usage)
- ✅ Authentication & authorization
- ✅ Activity logging
- ✅ Rate limiting
- ✅ HTTPS enforcement
- ✅ Secure headers
- ✅ Password security requirements
- ✅ Session management
- ✅ File upload validation
- ✅ CORS configuration
- ✅ Error handling (no sensitive data in errors)
- ✅ API versioning

---

## Additional Resources

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [Laravel Security Documentation](https://laravel.com/docs/security)
- [PHP Security Guide](https://www.php.net/manual/en/security.php)
- [API Security Best Practices](https://tools.ietf.org/html/rfc6819)

---

## Support

For security concerns or vulnerabilities:
1. Do NOT create public issues
2. Report to: security@yourdomain.com
3. Include: Description, reproduction steps, impact
4. Allow 30 days for response

---

**Last Updated**: 2026-05-04
**Version**: 1.0
**Status**: Production Ready
