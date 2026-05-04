# Development Guidelines - PHP Backend Dashboard

## Git Workflow

### Feature Branch Naming

```
feature/description          # New features
fix/description             # Bug fixes
docs/description            # Documentation
refactor/description        # Code refactoring
test/description            # Tests
chore/description           # Maintenance
```

### Commit Message Format

```
[PHASE] Type: Brief description

Detailed explanation of changes (optional)

- Point 1
- Point 2

Related to: #issue-number (if applicable)
```

### Commit Message Examples

**Good**:

```
[Phase 3] Feature: Add authentication endpoints

Implemented user registration, login, and logout with secure token-based authentication.
Added AuthenticationService for business logic and UserRepository for data access.

- User registration with email validation
- Login with bcrypt password verification
- Token generation with Laravel Sanctum
- Logout with token revocation

Follows REST API standards and Constitution principles.
```

**Bad**:

```
updated auth
fixed login
```

---

## Code Review Checklist

### Before Creating PR

- [ ] Branch created from latest main
- [ ] All tests pass: `composer test`
- [ ] Code formatted: `composer lint:check`
- [ ] No linting errors: `composer lint`
- [ ] Documentation updated
- [ ] Commit messages descriptive
- [ ] Constitution principles followed

### Code Review Points

- [ ] Follows Constitution principles
- [ ] Tests written (TDD approach)
- [ ] Code is clean and readable
- [ ] No security vulnerabilities
- [ ] No hardcoded secrets
- [ ] Documentation complete
- [ ] Handles errors properly
- [ ] Uses services/repositories pattern
- [ ] Consistent response format
- [ ] Performance considered

---

## Testing Guidelines

### TDD Workflow

1. Write failing test
2. Run test (should fail)
3. Write minimal code to pass
4. Run test (should pass)
5. Refactor if needed
6. Commit

### Test Structure

```php
public function test_feature_description(): void
{
    // Arrange: Set up test data
    $user = User::factory()->create();

    // Act: Perform the action
    $response = $this->actingAs($user)->getJson('/api/v1/profile');

    // Assert: Check the results
    $response->assertStatus(200)
        ->assertJson(['success' => true]);
}
```

### Test Naming Convention

```
test_[method]_[scenario]_[expected_result]

Examples:
test_user_can_register()
test_user_cannot_login_with_wrong_password()
test_authenticated_user_can_view_profile()
test_guest_cannot_access_dashboard()
```

### Minimum Coverage

- Aim for 80%+ code coverage
- All public methods should have tests
- All branches should be tested
- Edge cases should be tested

---

## Code Standards

### PHP Standards

- PSR-12: Extended Coding Style Guide
- PSR-4: Autoloading Standard
- Use type hints for all parameters and returns
- Use strict types: `declare(strict_types=1);`

### Naming Conventions

- Classes: PascalCase (UserController)
- Methods: camelCase (getUserProfile)
- Variables: camelCase (userName)
- Constants: UPPER_SNAKE_CASE (MAX_LOGIN_ATTEMPTS)
- Database columns: snake_case (user_id, email_verified_at)

### Code Comments

```php
// GOOD - Explains WHY
// We use bcrypt hashing to ensure passwords are irreversible
$password = Hash::make($request->password);

// BAD - Explains WHAT (obvious from code)
// Hash the password using bcrypt
$password = Hash::make($request->password);
```

### Class Documentation

```php
/**
 * Authentication Service
 *
 * Handles all authentication-related business logic.
 * Encapsulates registration, login, logout, and password management.
 */
class AuthenticationService { }
```

### Method Documentation

```php
/**
 * Register a new user
 *
 * @param array $data User data (name, email, password)
 * @return User The created user
 * @throws \Exception If email already exists
 */
public function register(array $data): User
{
}
```

---

## Security Guidelines

### When Writing Code

- [ ] Never hardcode secrets
- [ ] Always hash passwords
- [ ] Validate all inputs
- [ ] Use parameterized queries (Eloquent)
- [ ] Check authentication/authorization
- [ ] Log sensitive actions
- [ ] Return generic error messages
- [ ] Use HTTPS in production
- [ ] Add CSRF tokens
- [ ] Rate limit endpoints

### Security Checklist

```php
// GOOD - Secure
$user = User::where('email', $email)->first();
if ($user && Hash::check($password, $user->password)) {
    // Grant access
}

// BAD - Insecure
$user = User::where('email', $email)
    ->where('password', $password)
    ->first();
if ($user) {
    // Grant access
}
```

---

## Performance Guidelines

### Database

- Use eager loading: `with(['notifications', 'activityLogs'])`
- Add indexes for frequently queried columns
- Use pagination for large datasets
- Cache expensive queries
- Monitor query performance

### API Responses

- Return only necessary fields
- Implement pagination
- Use caching headers
- Compress responses
- Minimize database queries

### Code

- Avoid N+1 queries
- Cache computed values
- Use queues for long operations
- Defer heavy processing
- Profile code performance

---

## Documentation Standards

### README Files

- Installation instructions
- Configuration guide
- API documentation
- Testing guide
- Troubleshooting section
- Support information

### API Documentation

```
### Get User Profile
GET /api/v1/profile
Authorization: Bearer {token}

**Response (200)**:
{
  "success": true,
  "data": { ... }
}

**Error (401)**:
{
  "success": false,
  "message": "Unauthorized"
}
```

### Code Comments

- Explain complex logic
- Document assumptions
- Add TODO for future work
- Reference related code
- Include examples if helpful

---

## Debugging Tips

### Use Tinker

```bash
php artisan tinker

# Try code interactively
> $user = User::find(1);
> $user->notifications()->count();
```

### Check Logs

```bash
tail -f storage/logs/laravel.log
```

### Debug Queries

```php
// Enable query logging
DB::enableQueryLog();
$users = User::all();
dd(DB::getQueryLog());
```

### Use Laravel Debugbar

```php
// In .env
APP_DEBUG=true

// Show queries and request info in debug bar
```

---

## Common Patterns

### Service Usage

```php
// Inject service via constructor
public function __construct(AuthenticationService $authService)
{
    $this->authService = $authService;
}

// Use service method
$result = $this->authService->login($email, $password);
```

### Repository Usage

```php
// Inject repository via constructor
public function __construct(UserRepository $userRepository)
{
    $this->userRepository = $userRepository;
}

// Use repository methods
$user = $this->userRepository->findByEmail($email);
```

### Consistent Response

```php
// Success response
return ResponseHelper::success($data, 'Operation successful', 200);

// Error response
return ResponseHelper::error('Error message', 400);

// Validation error
return ResponseHelper::validationError($errors);
```

---

## Resources

- [Laravel Documentation](https://laravel.com/docs)
- [PHP Standards](https://www.php-fig.org/)
- [RESTful API Best Practices](https://restfulapi.net/)
- [SOLID Principles](https://en.wikipedia.org/wiki/SOLID)
- [Clean Code](https://www.oreilly.com/library/view/clean-code/9780136083238/)

---

**Version**: 1.0.0  
**Last Updated**: May 3, 2026
