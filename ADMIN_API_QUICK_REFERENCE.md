# Phase 7 - Admin API Quick Reference Guide

## 🚀 Quick Start for Admin Endpoints

All endpoints require:
- **Authentication**: Bearer token (obtained from login)
- **Authorization**: Admin role

### Base URL
```
http://localhost:8000/api/v1/admin
```

---

## 👥 User Management Endpoints

### 1. List All Users
```bash
GET /users?page=1&per_page=15&status=active

Query Parameters:
  - page: int (default: 1)
  - per_page: int (default: 15, max: 100)
  - status: string (optional: active, banned, suspended)

Response:
{
  "success": true,
  "message": "Users retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "is_banned": false,
      "is_suspended": false,
      ...
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 15,
    "total": 100,
    "last_page": 7
  }
}
```

### 2. Search Users
```bash
GET /users/search?q=john&page=1

Query Parameters:
  - q: string (search query, min 2 chars)
  - page: int (default: 1)

Response: Same structure as list users
```

### 3. Get User Details
```bash
GET /users/{id}

Response:
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "is_active": true,
    "is_banned": false,
    "is_suspended": false,
    "roles": [
      {
        "id": 1,
        "name": "User",
        "slug": "user"
      }
    ],
    "permissions": {
      "content": [
        {
          "slug": "content.manage",
          "name": "Manage Content",
          "granted_through": "role: User"
        }
      ]
    }
  }
}
```

### 4. Update User
```bash
PUT /users/{id}

Request Body:
{
  "name": "Jane Doe",
  "email": "jane@example.com"
}

Response: Updated user object
```

### 5. Assign Role
```bash
POST /users/{id}/roles

Request Body:
{
  "role_id": 2
}

Response:
{
  "success": true,
  "message": "Role assigned successfully",
  "data": {
    "id": 1,
    "name": "John Doe",
    "roles": [
      {
        "id": 2,
        "name": "Moderator"
      }
    ]
  }
}
```

### 6. Ban User
```bash
POST /users/{id}/ban

Request Body:
{
  "reason": "Violated community guidelines"
}

Response:
{
  "success": true,
  "message": "User banned successfully",
  "data": {
    "id": 1,
    "is_banned": true,
    "banned_at": "2026-05-05T10:30:00Z"
  }
}
```

### 7. Unban User
```bash
POST /users/{id}/unban

Response:
{
  "success": true,
  "message": "User unbanned successfully",
  "data": {
    "id": 1,
    "is_banned": false
  }
}
```

### 8. Suspend User
```bash
POST /users/{id}/suspend

Request Body:
{
  "reason": "Account under review"
}

Response:
{
  "success": true,
  "message": "User suspended successfully",
  "data": {
    "id": 1,
    "is_suspended": true,
    "suspended_at": "2026-05-05T10:30:00Z"
  }
}
```

### 9. Unsuspend User
```bash
POST /users/{id}/unsuspend

Response:
{
  "success": true,
  "message": "User unsuspended successfully",
  "data": {
    "id": 1,
    "is_suspended": false
  }
}
```

### 10. Delete User
```bash
DELETE /users/{id}

Response:
{
  "success": true,
  "message": "User deleted successfully"
}
```

### 11. Get User Activity Logs
```bash
GET /users/{id}/activities

Response:
{
  "success": true,
  "message": "Activities retrieved successfully",
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "action": "User logged in",
      "created_at": "2026-05-05T10:30:00Z"
    }
  ]
}
```

---

## 🔑 Role Management Endpoints

### 1. List All Roles
```bash
GET /roles

Response:
{
  "success": true,
  "message": "Roles retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "Administrator",
      "slug": "admin",
      "description": "Full system access",
      "is_active": true,
      "permission_count": 20,
      "user_count": 5
    }
  ]
}
```

---

## 📊 Statistics Endpoint

### Get System Statistics
```bash
GET /statistics

Response:
{
  "success": true,
  "message": "Statistics retrieved successfully",
  "data": {
    "total_users": 150,
    "active_users": 142,
    "banned_users": 5,
    "suspended_users": 3,
    "new_users_this_week": 12,
    "new_users_this_month": 45
  }
}
```

---

## 🧪 Example Usage with cURL

### Get Authorization Token
```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "password"
  }'
```

### List Users (with token)
```bash
curl -X GET http://localhost:8000/api/v1/admin/users \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Ban a User
```bash
curl -X POST http://localhost:8000/api/v1/admin/users/5/ban \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "reason": "Spam behavior"
  }'
```

### Search Users
```bash
curl -X GET "http://localhost:8000/api/v1/admin/users/search?q=john" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

---

## 🧪 Example Usage with Postman

### Setup
1. Create collection: "Admin API"
2. Create environment with variables:
   - `base_url`: http://localhost:8000
   - `token`: (obtained from login)

### Request Examples

#### List Users
```
Method: GET
URL: {{base_url}}/api/v1/admin/users?page=1&per_page=15
Headers:
  Authorization: Bearer {{token}}
```

#### Ban User
```
Method: POST
URL: {{base_url}}/api/v1/admin/users/5/ban
Headers:
  Authorization: Bearer {{token}}
  Content-Type: application/json
Body (raw JSON):
{
  "reason": "Violating terms of service"
}
```

#### Assign Role
```
Method: POST
URL: {{base_url}}/api/v1/admin/users/5/roles
Headers:
  Authorization: Bearer {{token}}
  Content-Type: application/json
Body (raw JSON):
{
  "role_id": 2
}
```

---

## 📈 Status Codes

| Code | Meaning | Example |
|------|---------|---------|
| 200 | Success | User listed/updated |
| 201 | Created | User created |
| 400 | Bad Request | Invalid input |
| 401 | Unauthorized | Missing token |
| 403 | Forbidden | Not admin role |
| 404 | Not Found | User doesn't exist |
| 422 | Validation Error | Invalid email format |
| 500 | Server Error | Database error |

---

## 🔐 Available Roles

### Administrator
- Full access to all endpoints
- Can manage all users
- Can manage roles
- Can view all statistics

**Permissions**: All 20+ system permissions

### Moderator
- Can list users
- Can ban/suspend users
- Can manage content
- Can delete content
- Can view dashboard
- Can view analytics

**Permissions**: users.read, users.ban, content.manage, content.delete, dashboard.access, analytics.read

### User
- Can manage their own content
- Can access dashboard

**Permissions**: content.manage, dashboard.access

---

## 🛡️ Protection Rules

### Self-Protection
- ✅ Admin cannot ban themselves
- ✅ Admin cannot suspend themselves
- ✅ Admin cannot delete themselves

### Verification
- ✅ User must exist
- ✅ User must not already be banned (for ban request)
- ✅ User must be banned (for unban request)
- ✅ Admin must be authenticated

---

## 💡 Common Use Cases

### Create Admin User and Log In
```bash
# 1. Register as admin (one-time setup)
POST /auth/register
{
  "name": "Admin User",
  "email": "admin@example.com",
  "password": "secure_password"
}

# 2. Assign admin role (done by system/migration)
# Admin role assigned via RoleSeeder

# 3. Login and get token
POST /auth/login
{
  "email": "admin@example.com",
  "password": "secure_password"
}

# 4. Use token for admin endpoints
GET /admin/users
Authorization: Bearer {token}
```

### Handle User Violation
```bash
# 1. Get user details
GET /admin/users/123

# 2. Check user activity logs
GET /admin/users/123/activities

# 3. Ban user
POST /admin/users/123/ban
{
  "reason": "Multiple violations of community guidelines"
}

# 4. Verify ban
GET /admin/users/123
# Should show is_banned: true
```

### Promote User to Moderator
```bash
# 1. Get moderator role ID
GET /admin/roles
# Find role with slug: "moderator", id: 2

# 2. Assign moderator role
POST /admin/users/123/roles
{
  "role_id": 2
}

# 3. Verify assignment
GET /admin/users/123
# Should show moderator role in roles array
```

---

## 🔗 Related Documentation

- Full Admin API Documentation: See IMPLEMENTATION_PLAN.md
- RBAC System Guide: See backend/README.md
- Role & Permission Setup: See PHASE_7_COMPLETION.md

---

**Version**: 1.0
**Last Updated**: May 5, 2026
**Phase**: 7 - Admin & Role Management
