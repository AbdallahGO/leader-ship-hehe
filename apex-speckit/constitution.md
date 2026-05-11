# Apex Leadership Summit — Constitution

> This document defines the non-negotiable principles for every AI agent working on this project.
> Every decision, implementation, and code generation MUST respect these rules.
> Never override. Never skip. Never assume exceptions.

---

## Project Identity

**Product**: Apex Leadership Summit 2026 — Full-stack web application  
**Stack**: Node.js + Express (backend) · PostgreSQL (database) · Next.js + React (frontend) · Stripe (payments) · SendGrid (email)  
**AI Tooling**: GitHub Spec Kit with Claude Code / GitHub Copilot / Gemini CLI

---

## 1. Architecture Principles

- **Separation of concerns**: Frontend never talks directly to the database. All data flows through the REST API.
- **Single source of truth**: Database is the source of truth. Frontend state is derived from API responses.
- **Stateless API**: The backend is stateless. Session state is carried in JWT tokens only.
- **Environment-driven config**: No hardcoded secrets, URLs, or credentials. All config via `.env`.
- **Fail loudly in development, fail gracefully in production**: Dev shows full error stacks. Prod returns safe error messages.

---

## 2. Security Rules (Non-Negotiable)

- **Passwords**: Always hashed with `bcryptjs` at a minimum of 12 salt rounds. Raw passwords are never logged or stored.
- **JWT**: Stored exclusively in `httpOnly`, `secure`, `sameSite=strict` cookies. Never in `localStorage` or `sessionStorage`.
- **Input validation**: Every API endpoint uses `express-validator`. No raw `req.body` values are inserted into SQL.
- **SQL queries**: Always use parameterized queries via `pg`. Never string-concatenate user input into SQL.
- **CORS**: Restricted to `FRONTEND_URL` env variable only. No wildcard `*` in production.
- **Rate limiting**: Auth endpoints (`/register`, `/login`, `/forgot-password`) are rate-limited to 10 requests per 15 minutes per IP.
- **Stripe**: Raw card data never touches our server. Stripe.js handles card collection client-side. Webhook signature verified with `stripe.webhooks.constructEvent()`.
- **Admin routes**: Always guarded by both `authMiddleware` AND `roleCheck('admin')`. Never expose admin functionality to regular users.

---

## 3. Database Rules

- **UUIDs**: All primary keys use `UUID` generated with `gen_random_uuid()`. Never use auto-increment integers.
- **Timestamps**: Every table has `created_at TIMESTAMP DEFAULT NOW()`. Tables that update records also have `updated_at`.
- **Soft deletes**: Never `DELETE` user records. Use `is_active = false` or `deleted_at` timestamp.
- **Migrations**: All schema changes go through versioned migration files in `database/migrations/`. Never alter production schema manually.
- **Seeds**: Seed data (plans, speakers, sponsors, promo codes) lives in `database/seed.sql` and is idempotent (safe to run multiple times).

---

## 4. API Design Rules

- **REST conventions**: `GET` = read, `POST` = create, `PUT` = full update, `PATCH` = partial update, `DELETE` = remove.
- **Response shape**: All responses follow `{ success: true, data: {...} }` on success and `{ success: false, error: "message", details: [...] }` on failure.
- **HTTP status codes**: `200` OK, `201` Created, `400` Bad Request (validation), `401` Unauthorized, `403` Forbidden, `404` Not Found, `409` Conflict (duplicate), `500` Server Error.
- **Pagination**: All list endpoints (`/admin/orders`, `/admin/users`) support `?page=1&limit=20`.
- **Versioning**: All routes are prefixed with `/api`. Future versions use `/api/v2`.

---

## 5. Frontend Rules

- **No business logic in components**: Components render UI and call API functions. Logic lives in `lib/api.js` and `lib/auth.js`.
- **No direct `fetch()` in components**: All API calls go through the typed wrappers in `lib/api.js`.
- **Auth state**: Managed via React Context (`AuthContext`). Never stored in component-level state or localStorage.
- **Forms**: All form submissions show a loading state. All errors display inline next to the relevant field.
- **Accessibility**: Every interactive element has an `aria-label` or visible label. Modals trap focus. Color is never the only indicator of state.
- **Images**: Speaker and sponsor images served via `next/image` with `alt` text. Lazy-loaded below the fold.

---

## 6. Code Quality Rules

- **One responsibility per file**: Routes handle HTTP. Services handle business logic. Models handle database queries. Never mix.
- **Error handling**: Every `async` route handler is wrapped in `try/catch`. Database errors are caught and returned as `500`.
- **Environment validation**: On server start, check that all required env variables exist. Throw an error and refuse to start if any are missing.
- **No `console.log` in production**: Use a proper logger (morgan for HTTP, console.error for exceptions). Remove all debug logs before deploying.
- **Comments**: Write comments for WHY, not WHAT. The code explains what. Comments explain intent, tradeoffs, and non-obvious decisions.

---

## 7. Testing Requirements

- Every API endpoint has at least one happy-path test and one error-path test.
- Auth middleware is tested in isolation.
- Stripe webhook handler is tested with mock events.
- No feature is considered complete until its tests pass.

---

## 8. Deployment Rules

- **Backend**: Railway (Node.js service + managed PostgreSQL add-on).
- **Frontend**: Vercel (Next.js, automatic preview deployments on PRs).
- **Secrets**: Stored in Railway and Vercel environment variables dashboards. Never in code or `.env` files committed to git.
- **`.gitignore`**: `.env`, `node_modules/`, `.next/`, `*.log` are always ignored.
- **Migrations run before server starts**: The Railway start command runs `node database/migrate.js && node server.js`.

---

*Last updated: May 2026 · Apex Leadership Network*
