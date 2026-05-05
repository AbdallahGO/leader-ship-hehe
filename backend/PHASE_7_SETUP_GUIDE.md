# Phase 7 - Setup & Activation Guide

## Step-by-Step Completion Instructions

### Prerequisites
- PHP 8.3+
- Laravel 11
- Composer
- MySQL database

---

## Step 1: Install Dependencies

```bash
cd backend
composer install
```

---

## Step 2: Environment Setup

Copy and configure environment file:

```bash
cp .env.example .env
```

Edit `.env` with your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=leadership_dashboard
DB_USERNAME=root
DB_PASSWORD=

APP_KEY=base64:...  # Generate with: php artisan key:generate
```

---

## Step 3: Middleware Registration ✅

Middleware is registered in `bootstrap/app.php` - already done.

The following middleware aliases are available:
- `role` - RoleMiddleware (check user roles)
- `permission` - PermissionMiddleware (check permissions)
- `auth` - Authentication check

---

## Step 4: Run Database Migrations

Execute all migrations to create tables:

```bash
php artisan migrate

# Or migrate specific batch:
php artisan migrate --step=1
php artisan migrate --step=2
```

**What gets created:**
- users table (with new fields: is_banned, is_suspended, banned_at, suspended_at)
- roles table
- permissions table
- role_permissions pivot table
- user_roles pivot table
- user_permissions pivot table
- (plus activity_logs, notifications, sessions, etc.)

---

## Step 5: Run Database Seeders

Seed default roles and permissions:

```bash
php artisan db:seed

# Or seed specific seeders:
php artisan db:seed --class=PermissionSeeder
php artisan db:seed --class=RoleSeeder
```

**What gets seeded:**
- 20 permissions (across 6 categories)
- 3 roles (Administrator, Moderator, User)
- Role-permission assignments

---

## Step 6: Generate Application Key

```bash
php artisan key:generate
```

---

## Step 7: Create Initial Admin User

Use Laravel tinker to create admin:

```bash
php artisan tinker

>>> $admin = \App\Models\User::create([
    'name' => 'Admin User',
    'email' => 'admin@example.com',
    'password' => bcrypt('admin_password'),
    'role' => 'admin'
]);

>>> $admin->roles()->attach(1); // Attach admin role

>>> exit
```

Or use SQL directly:

```sql
INSERT INTO users (name, email, password, role, created_at, updated_at)
VALUES ('Admin User', 'admin@example.com', '$2y$12$...', 'admin', NOW(), NOW());

INSERT INTO user_roles (user_id, role_id, created_at, updated_at)
VALUES (1, 1, NOW(), NOW());
```

---

## Step 8: Run Tests

Run the complete test suite:

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/AdminTest.php

# Run with coverage report
php artisan test --coverage

# Run specific test class
php artisan test --filter=AdminTest
```

**Expected Results:**
- 40+ tests pass
- All admin endpoints tested
- Authorization tests pass
- Self-protection rules verified

---

## Step 9: Start Development Server

```bash
php artisan serve

# Or specify port
php artisan serve --port=8001
```

Server will be available at: `http://localhost:8000`

---

## Step 10: Test Admin Endpoints

### Login to get token

```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "admin_password"
  }'
```

Response will include `access_token`. Copy it.

### List Users

```bash
curl -X GET http://localhost:8000/api/v1/admin/users \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Get Admin Statistics

```bash
curl -X GET http://localhost:8000/api/v1/admin/statistics \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Ban a User

```bash
curl -X POST http://localhost:8000/api/v1/admin/users/2/ban \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "reason": "Violation of community guidelines"
  }'
```

---

## Step 11: Verify Functionality

Check that all components work:

### ✅ Database
```bash
php artisan tinker

>>> \App\Models\Role::all();
>>> \App\Models\Permission::all();
>>> \App\Models\User::all();
```

### ✅ Authorization
```bash
php artisan tinker

>>> $user = \App\Models\User::find(1);
>>> $user->hasRole('admin');
>>> $user->hasPermission('users.read');
```

### ✅ API Endpoints
Test all 13 admin endpoints with Postman or curl.

### ✅ Tests
```bash
php artisan test

# Should see all tests pass
Tests:  40 passed
```

---

## Step 12: Update Main README

Add the following to your main README.md:

```markdown
## Admin Features (Phase 7)

### User Management API

All admin endpoints require authentication and admin role.

#### Endpoints

**User Management:**
- `GET /api/v1/admin/users` - List all users
- `GET /api/v1/admin/users/search?q=name` - Search users
- `GET /api/v1/admin/users/{id}` - Get user details
- `PUT /api/v1/admin/users/{id}` - Update user
- `POST /api/v1/admin/users/{id}/roles` - Assign role
- `DELETE /api/v1/admin/users/{id}` - Delete user

**User Actions:**
- `POST /api/v1/admin/users/{id}/ban` - Ban user
- `POST /api/v1/admin/users/{id}/unban` - Unban user
- `POST /api/v1/admin/users/{id}/suspend` - Suspend user
- `POST /api/v1/admin/users/{id}/unsuspend` - Unsuspend user

**System:**
- `GET /api/v1/admin/roles` - List roles
- `GET /api/v1/admin/statistics` - Get statistics
- `GET /api/v1/admin/users/{id}/activities` - Get user activities

### Default Roles

1. **Administrator** - Full access
2. **Moderator** - Content and user moderation
3. **User** - Basic user permissions

### Permissions (20 total)

Users, Roles, Permissions, Dashboard, Content, System management.

See `ADMIN_API_QUICK_REFERENCE.md` for detailed usage.
```

---

## Complete Checklist

- [ ] Dependencies installed (`composer install`)
- [ ] Environment file configured (`.env`)
- [ ] Middleware registered (`bootstrap/app.php`)
- [ ] Migrations run (`php artisan migrate`)
- [ ] Seeders run (`php artisan db:seed`)
- [ ] Application key generated (`php artisan key:generate`)
- [ ] Admin user created
- [ ] All tests passing (`php artisan test`)
- [ ] Server running (`php artisan serve`)
- [ ] Endpoints verified (test with curl/Postman)
- [ ] README updated

---

## Troubleshooting

### "Class not found" errors
```bash
composer dump-autoload
```

### Migration errors
```bash
# Rollback and re-run
php artisan migrate:rollback
php artisan migrate
```

### Middleware not working
- Check `bootstrap/app.php` is configured
- Verify middleware file exists
- Check routes use middleware

### Tests failing
```bash
# Clear cache and retry
php artisan cache:clear
php artisan test
```

### Database errors
- Check DB credentials in `.env`
- Ensure database exists
- Run `php artisan migrate:fresh` if needed

---

## Next Phase

**Phase 8: Testing & Quality Assurance**

Once Phase 7 is fully operational, move to Phase 8 for:
- Code coverage analysis
- Performance optimization
- Security audit
- Documentation updates
- Integration testing

---

## Support Files

Reference these for detailed information:
- `PHASE_7_COMPLETION.md` - Technical details
- `PHASE_7_SUMMARY.md` - Overview
- `ADMIN_API_QUICK_REFERENCE.md` - API usage
- `backend/README.md` - Backend setup
- `SECURITY.md` - Security features

---

## Timeline

- **Step 1-2**: 5 minutes (setup)
- **Step 3-7**: 10 minutes (configuration)
- **Step 8**: 5 minutes (testing)
- **Step 9-10**: 5 minutes (verification)
- **Step 11-12**: 10 minutes (documentation)

**Total**: ~40 minutes for complete Phase 7 activation

---

**Status**: Ready for Implementation
**Last Updated**: May 5, 2026
**Version**: 1.0
