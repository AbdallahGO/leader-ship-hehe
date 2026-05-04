# PHP Backend User Dashboard

A production-ready PHP Laravel backend system for a user dashboard application with secure authentication, REST APIs, and scalable architecture.

## Project Overview

This backend provides:

- **Secure Authentication** - User registration, login, and password management
- **User Dashboard APIs** - Dashboard data, statistics, and activity tracking
- **Profile Management** - User profile viewing and updates
- **Notification System** - User notifications with read/unread status
- **Admin Features** - User management and role-based access control
- **Security-First** - Rate limiting, CSRF protection, bcrypt password hashing

## Tech Stack

- **Language**: PHP 8.3+
- **Framework**: Laravel 11
- **Database**: MySQL 8+
- **Authentication**: Laravel Sanctum (API Tokens)
- **Caching**: Redis
- **Testing**: PHPUnit, Pest PHP
- **API Style**: REST with consistent JSON responses

## Project Structure

```
backend/
├── app/
│   ├── Controllers/         # HTTP request handlers
│   ├── Models/             # Database models
│   ├── Services/           # Business logic
│   ├── Repositories/       # Data access abstraction
│   ├── Middleware/         # HTTP middleware
│   └── Helpers/            # Helper utilities
├── database/
│   ├── migrations/         # Database schema
│   └── seeders/           # Test data
├── routes/                 # API route definitions
├── config/                 # Configuration files
├── tests/
│   ├── Unit/              # Unit tests
│   └── Feature/           # Integration tests
├── storage/               # Application storage
└── resources/             # Views and resources
```

## Setup Instructions

### Prerequisites

- PHP 8.3 or higher
- MySQL 8.0 or higher
- Composer
- Node.js (optional, for frontend)

### Installation

1. **Clone the repository**

   ```bash
   git clone <repository-url> backend
   cd backend
   ```

2. **Install dependencies**

   ```bash
   composer install
   ```

3. **Set up environment variables**

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure database**
   Edit `.env` file with your database credentials:

   ```
   DB_HOST=127.0.0.1
   DB_DATABASE=php_dashboard
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Run migrations**

   ```bash
   php artisan migrate
   ```

6. **Run seeders (optional)**

   ```bash
   php artisan db:seed
   ```

7. **Start development server**

   ```bash
   php artisan serve
   ```

   The API will be available at `http://localhost:8000/api/v1`

## API Documentation

### Authentication Endpoints

#### Register User

```http
POST /api/v1/auth/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "SecurePassword123",
  "password_confirmation": "SecurePassword123"
}
```

**Success Response (201):**

```json
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  }
}
```

#### Login User

```http
POST /api/v1/auth/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "SecurePassword123"
}
```

**Success Response (200):**

```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role": "user"
    },
    "expires_at": "2024-05-04T12:00:00Z"
  }
}
```

#### Get Current User

```http
GET /api/v1/auth/me
Authorization: Bearer {token}
```

**Success Response (200):**

```json
{
  "success": true,
  "message": "User retrieved successfully",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "avatar": null,
    "role": "user",
    "created_at": "2024-05-03T12:00:00Z"
  }
}
```

#### Logout User

```http
POST /api/v1/auth/logout
Authorization: Bearer {token}
```

#### Change Password

```http
POST /api/v1/auth/change-password
Authorization: Bearer {token}
Content-Type: application/json

{
  "current_password": "CurrentPassword123",
  "password": "NewPassword123",
  "password_confirmation": "NewPassword123"
}
```

### Dashboard Endpoints

#### Get Dashboard Data

```http
GET /api/v1/dashboard
Authorization: Bearer {token}
```

#### Get Statistics

```http
GET /api/v1/dashboard/statistics
Authorization: Bearer {token}
```

#### Get Activities

```http
GET /api/v1/dashboard/activities?per_page=10
Authorization: Bearer {token}
```

### Profile Endpoints

#### Get Profile

```http
GET /api/v1/profile
Authorization: Bearer {token}
```

#### Update Profile

```http
PUT /api/v1/profile
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Jane Doe",
  "email": "jane@example.com"
}
```

#### Upload Avatar

```http
POST /api/v1/profile/avatar
Authorization: Bearer {token}
Content-Type: multipart/form-data

avatar: <file>
```

### Notification Endpoints

#### Get Notifications

```http
GET /api/v1/notifications?per_page=10&is_read=false
Authorization: Bearer {token}
```

#### Get Single Notification

```http
GET /api/v1/notifications/{id}
Authorization: Bearer {token}
```

#### Mark as Read

```http
PUT /api/v1/notifications/{id}/read
Authorization: Bearer {token}
```

#### Delete Notification

```http
DELETE /api/v1/notifications/{id}
Authorization: Bearer {token}
```

## Standard Response Format

All API responses follow a consistent JSON structure:

**Success Response:**

```json
{
  "success": true,
  "message": "Request successful",
  "data": { ... },
  "timestamp": "2024-05-03T12:00:00Z",
  "version": "v1"
}
```

**Error Response:**

```json
{
  "success": false,
  "message": "Error message",
  "errors": { ... },
  "timestamp": "2024-05-03T12:00:00Z",
  "version": "v1"
}
```

## Status Codes

- `200` - OK (successful GET/PUT/DELETE)
- `201` - Created (successful POST)
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `409` - Conflict (resource already exists)
- `422` - Unprocessable Entity (validation error)
- `500` - Internal Server Error

## Testing

### Run Tests

```bash
composer test
```

### Run Tests with Coverage

```bash
composer test:coverage
```

### Run Linter

```bash
composer lint
```

### Check Linter

```bash
composer lint:check
```

## Security Features

- ✅ Password hashing with bcrypt
- ✅ CSRF token protection
- ✅ Rate limiting
- ✅ SQL injection prevention (parameterized queries)
- ✅ XSS protection
- ✅ CORS configuration
- ✅ Secure session management
- ✅ API token authentication (Laravel Sanctum)
- ✅ Input validation
- ✅ Activity logging

## Database Schema

### Users Table

```sql
CREATE TABLE users (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) UNIQUE NOT NULL,
  email_verified_at TIMESTAMP NULL,
  password VARCHAR(255) NOT NULL,
  avatar VARCHAR(255) NULL,
  role ENUM('user', 'moderator', 'admin') DEFAULT 'user',
  remember_token VARCHAR(100) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_name (name),
  KEY idx_email (email),
  KEY idx_role (role),
  KEY idx_role_created (role, created_at)
);
```

### Other Tables

- **sessions** - User login sessions
- **notifications** - User notifications
- **activity_logs** - User activity audit trail

## Development Workflow

1. **Create feature branch**: `git checkout -b feature/new-feature`
2. **Make changes** following the constitution principles
3. **Write tests**: Ensure 80%+ code coverage
4. **Run tests**: `composer test`
5. **Check linting**: `composer lint:check`
6. **Commit changes**: `git commit -m "Description of changes"`
7. **Create pull request** for review
8. **Merge to main** after approval

## Constitution Principles

This project adheres to the **PHP Backend User Dashboard Constitution**:

1. **Secure Backend Architecture** - Security in every layer
2. **Clean Code** - SOLID principles, meaningful names
3. **REST API Standards** - Proper HTTP methods and status codes
4. **Performance Optimization** - Efficient queries and caching
5. **Scalable Architecture** - Service-based design
6. **Automated Testing** - TDD, minimum 80% coverage
7. **Security-First Development** - CSRF, CORS, rate limiting
8. **Consistent JSON Responses** - Standard format for all endpoints
9. **Modular Services** - Single responsibility principle
10. **Database Optimization** - Proper indexing and queries

## Future Features

- [x] User authentication (registration, login, logout)
- [x] Profile management
- [x] Dashboard with statistics
- [x] Notification system
- [ ] Email verification
- [ ] Password reset with email
- [ ] Two-factor authentication
- [ ] Admin user management
- [ ] File upload system
- [ ] WebSockets for real-time updates
- [ ] API rate limiting per user
- [ ] Request logging and monitoring
- [ ] Swagger/OpenAPI documentation

## Troubleshooting

### Database connection error

- Check `.env` file DB settings
- Ensure MySQL is running
- Verify database exists and credentials are correct

### Port already in use

- Change port: `php artisan serve --port=8001`
- Or stop process using port 8000

### Migrations fail

- Check database credentials
- Ensure database user has CREATE privileges
- Run: `php artisan migrate:reset` then `php artisan migrate`

## Contributing

1. Follow the Constitution principles
2. Write tests for new features
3. Ensure all tests pass
4. Create pull request with detailed description

## License

MIT License - see LICENSE file for details

## Support

For issues and questions:

1. Check the troubleshooting section
2. Review existing issues
3. Create a new issue with detailed description

---

**Project Version**: 1.0.0  
**Last Updated**: May 3, 2026
