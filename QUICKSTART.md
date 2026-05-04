# Quick Start Guide - PHP Backend Dashboard

## ⚡ Get Started in 5 Minutes

### 1. Install Dependencies

```bash
cd backend
composer install
```

### 2. Setup Environment

```bash
cp .env.example .env
php artisan key:generate
```

### 3. Configure Database

Edit `.env` and set your database credentials:

```
DB_HOST=127.0.0.1
DB_DATABASE=php_dashboard
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Run Migrations

```bash
php artisan migrate
```

### 5. Start Server

```bash
php artisan serve
```

**API Available at**: `http://localhost:8000/api/v1`

---

## 📝 Common Commands

### Development

```bash
php artisan serve              # Start dev server on port 8000
php artisan serve --port=8001 # Start on different port
php artisan tinker             # Interactive shell
```

### Database

```bash
php artisan migrate            # Run all migrations
php artisan migrate:refresh    # Clear and remigrate
php artisan db:seed            # Run all seeders
```

### Testing

```bash
composer test                  # Run all tests
composer test:coverage         # With code coverage
composer lint                  # Fix linting issues
composer lint:check            # Check without fixing
```

### Code Generation

```bash
php artisan make:controller NameController
php artisan make:model ModelName -m
php artisan make:migration CreateTableName
php artisan make:seeder SeederName
php artisan make:middleware MiddlewareName
```

---

## 🧪 Test the API

### 1. Register a User

```bash
curl -X POST http://localhost:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "SecurePassword123",
    "password_confirmation": "SecurePassword123"
  }'
```

### 2. Login

```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "SecurePassword123"
  }'
```

### 3. Use Token to Get Profile

```bash
curl -X GET http://localhost:8000/api/v1/auth/me \
  -H "Authorization: Bearer {your-token-here}"
```

---

## 📚 Important Files

- **Constitution**: `CONSTITUTION.md` - Development principles
- **Setup Guide**: `backend/README.md` - Complete documentation
- **Progress**: `IMPLEMENTATION_PLAN.md` - What's done and what's next
- **Summary**: `PROJECT_SUMMARY.md` - Overview of the project

---

## 🔑 Key Principles

1. **Test First** - Write tests before code (TDD)
2. **Follow Constitution** - Adhere to 10 core principles
3. **Use Services** - Business logic goes in services
4. **Use Repositories** - Database access via repositories
5. **Consistent Responses** - Use ResponseHelper for JSON
6. **Security** - Always think security first
7. **Documentation** - Comment your code
8. **Clean Code** - Keep it simple and readable

---

## 🛠️ Architecture Overview

### Request Flow

```
HTTP Request
    ↓
Route (api.php)
    ↓
Controller (Handle HTTP)
    ↓
Service (Business Logic)
    ↓
Repository (Database Access)
    ↓
Model (Eloquent)
    ↓
Database (MySQL)
```

### Response Format

```json
{
  "success": true,
  "message": "Operation successful",
  "data": {
    /* data here */
  },
  "timestamp": "2024-05-03T12:00:00Z",
  "version": "v1"
}
```

---

## 📋 Development Checklist

Before pushing code:

- [ ] Tests written (TDD)
- [ ] All tests pass: `composer test`
- [ ] Code formatted: `composer lint`
- [ ] No linting errors: `composer lint:check`
- [ ] Documentation updated
- [ ] Code follows constitution
- [ ] Descriptive commit message

---

## 🐛 Troubleshooting

### Port 8000 already in use

```bash
php artisan serve --port=8001
```

### Database connection error

- Check `.env` file
- Ensure MySQL is running
- Verify database exists

### Migration errors

```bash
php artisan migrate:reset  # Clear migrations
php artisan migrate        # Re-run
```

### Clear caches

```bash
php artisan cache:clear
php artisan config:cache
php artisan view:cache
```

---

## 📖 Next Steps

1. Read `CONSTITUTION.md` to understand principles
2. Read `backend/README.md` for full documentation
3. Check `IMPLEMENTATION_PLAN.md` for progress
4. Review completed Phase 3 (Authentication)
5. Work on Phase 4 (Dashboard APIs)

---

## 🚀 Project Phases

| Phase | Status   | Work           |
| ----- | -------- | -------------- |
| 1     | ✅ Done  | Foundation     |
| 2     | ✅ Done  | Database       |
| 3     | ✅ Done  | Authentication |
| 4     | 🔄 Ready | Dashboard APIs |
| 5     | ⏳ Next  | Security Layer |
| 6     | ⏳ Next  | File Uploads   |
| 7     | ⏳ Next  | Admin Features |
| 8     | ⏳ Next  | Testing & QA   |
| 9     | ⏳ Next  | Deployment     |

---

## 📞 Need Help?

1. Check `backend/README.md` for comprehensive docs
2. Review `IMPLEMENTATION_PLAN.md` for what's been done
3. Look at test examples in `tests/Feature/`
4. Check git commits for implementation patterns
5. Read code comments (they explain "why")

---

**Version**: 1.0.0  
**Last Updated**: May 3, 2026  
**Framework**: Laravel 11  
**PHP**: 8.3+
