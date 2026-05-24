# Phase 2 — Spec: User Authentication
**Spec Kit artifact** · Run `/speckit.specify` then `/speckit.clarify`

## Overview

Users can create accounts, sign in, and stay signed in across sessions.
Passwords are hashed. Sessions use JWT in httpOnly cookies.
The frontend auth modal connects to real API endpoints.

## Functional Requirements

### F1 — Registration
`POST /api/auth/register` accepts: `first_name`, `last_name`, `email`, `password`, `phone` (optional), `organization` (optional), `country`, `city` (optional).
Validates email format and uniqueness. Password minimum 8 characters.
Hashes password with bcrypt (12 rounds). Inserts user. Returns JWT cookie + user object (no password field).
Sends welcome email via SendGrid.

### F2 — Login
`POST /api/auth/login` accepts `email`, `password`.
Finds user by email. Compares bcrypt hash. If mismatch → 401 with generic message (never specify which field is wrong).
On success: sets `token` httpOnly cookie with JWT. Returns user object.

### F3 — Logout
`POST /api/auth/logout` clears the `token` cookie. Returns `{ success: true }`.

### F4 — Get Current User
`GET /api/auth/me` — protected route.
Returns the authenticated user's profile (no password).

### F5 — Forgot Password
`POST /api/auth/forgot-password` accepts `email`.
Always returns 200 (prevents email enumeration). If email exists: generates reset token, saves hashed version + expiry to user record, sends password reset email with link.

### F6 — Reset Password
`POST /api/auth/reset-password` accepts `token`, `password`.
Validates token exists, matches hash, is not expired (1 hour TTL).
Hashes new password. Clears reset token fields. Returns 200.

### F7 — Auth Middleware
`middleware/auth.js` reads JWT from `req.cookies.token`.
Verifies with `JWT_SECRET`. Attaches `req.user = { id, email, role }`.
On failure → 401. Used by all protected routes.

### F8 — Frontend Integration
Sign In tab in auth modal → `POST /api/auth/login` → store user in localStorage/session → update navbar.
Register tab → `POST /api/auth/register` → same flow.
Navbar shows user first name + avatar initials after login.
Sign In button replaced by user menu with Dashboard + Logout.
Auth modal in leadership-summit.html connects to real API endpoints.

## Acceptance Criteria
- [ ] Register with valid data → user in DB, cookie set, welcome email sent
- [ ] Register with duplicate email → 409 conflict response
- [ ] Login with correct credentials → cookie set, user returned
- [ ] Login with wrong password → 401, same message as wrong email
- [ ] `GET /api/auth/me` without cookie → 401
- [ ] `GET /api/auth/me` with valid cookie → user object returned
- [ ] Logout clears cookie, subsequent /me returns 401
- [ ] Forgot password with unknown email → still returns 200
- [ ] Reset password with expired token → 400 error
