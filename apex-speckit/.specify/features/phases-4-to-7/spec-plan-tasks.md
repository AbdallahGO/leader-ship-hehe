# Phase 4 — Spec + Plan + Tasks: Email Confirmations
**Spec Kit artifact** · `/speckit.specify` → `/speckit.plan` → `/speckit.tasks` → `/speckit.implement`

---

## Spec

### Overview
Transactional emails are sent after every key user action: registration, ticket purchase, password reset, and host application submission.
SendGrid handles delivery. HTML templates are consistent with the site's dark gold aesthetic.

### Functional Requirements

**F1 — Welcome Email** (triggered after `/auth/register`)
To: new user's email. Subject: "Welcome to the Apex Leadership Summit 2026".
Body: user's first name, login link, brief what-to-expect message, Apex logo header.

**F2 — Order Receipt Email** (triggered after `payment_intent.succeeded` webhook)
To: order owner's email. Subject: "Your ticket is confirmed — Apex Summit 2026 · Order #ALS-XXXXXX".
Body: order number, plan name, quantity, total paid, event date/location, each ticket code + QR code image embedded as inline attachment (CID), download/add-to-wallet CTA.

**F3 — Password Reset Email** (triggered after `/auth/forgot-password`)
To: requesting user. Subject: "Reset your Apex Summit password".
Body: reset link with token (expires in 1 hour), warning that link is single-use.

**F4 — Host Application Confirmation** (triggered after `/host/apply`)
To: applicant. Subject: "Host application received — Apex Summit 2026".
Body: confirmation of submission, expected review timeline (5–7 days), support email.

### Acceptance Criteria
- [ ] Welcome email arrives after registration (check SendGrid activity)
- [ ] Receipt email contains correct order number and QR code image
- [ ] Password reset link is functional and single-use
- [ ] Host confirmation email arrives after form submission
- [ ] All emails render correctly on mobile (tested in Litmus or Email on Acid)
- [ ] No email is awaited synchronously — all are fire-and-forget

---

## Technical Plan

### Email Service Architecture

```
services/email.js
  ├── sendWelcomeEmail(user)
  ├── sendReceiptEmail(user, order, tickets)
  ├── sendResetEmail(user, resetToken)
  └── sendHostConfirmationEmail(user, application)

Each function:
  1. Builds HTML from template string
  2. Calls sgMail.send({ to, from, subject, html, attachments? })
  3. Does NOT throw — catches errors and logs them (email failure ≠ request failure)
```

### QR Code as Inline Image

```js
// Generate QR as buffer
const qrBuffer = await QRCode.toBuffer(ticketData, { type: 'png', width: 200 });

// Attach as inline image with Content-ID
attachments: [{
  content: qrBuffer.toString('base64'),
  filename: `ticket-${ticketCode}.png`,
  type: 'image/png',
  disposition: 'inline',
  contentId: `qr-${ticketCode}`
}]

// Reference in HTML template
<img src="cid:qr-${ticketCode}" alt="Your ticket QR code" />
```

### Template Strategy

Use tagged template literals in JavaScript (no external template engine needed for this scale).
Each template function receives data and returns an HTML string.
Templates share a common header/footer wrapper function.

---

## Tasks

### Task 4.1 — Build email service base

**File**: `backend/services/email.js`

Initialize `@sendgrid/mail` with `process.env.SENDGRID_API_KEY`.
Create `emailWrapper(content)` function that wraps content in shared HTML header + footer (logo, gold accent, footer text).
Export `sendEmail(to, subject, html, attachments=[])` base function — always wraps in try/catch, logs errors but doesn't throw.

### Task 4.2 — Build welcome email template + trigger

**File**: `backend/services/email.js` + `backend/routes/auth.js`

`sendWelcomeEmail(user)`: builds HTML with user.first_name, login link (`FRONTEND_URL + '/login'`), event date.
Call after successful INSERT in `/auth/register` — fire-and-forget (no `await`).

**Verify**: Register new user → SendGrid activity log shows delivered email.

### Task 4.3 — Build receipt email with QR attachment

**File**: `backend/services/email.js` + `backend/routes/webhooks.js`

`sendReceiptEmail(user, order, tickets)`: builds HTML table of order details.
For each ticket: generate QR buffer, add as inline attachment, embed `<img src="cid:...">` in HTML.
Call from `payment_intent.succeeded` webhook handler after creating tickets.

**Verify**: Purchase flow → email received with embedded QR code image.

### Task 4.4 — Build password reset email + trigger

**File**: `backend/services/email.js` + `backend/routes/auth.js`

`sendResetEmail(user, plainToken)`: HTML with reset link `FRONTEND_URL + '/reset-password?token=' + plainToken`, 1-hour expiry warning.
Call from `/auth/forgot-password` handler — fire-and-forget.

**Verify**: Forgot password request → email received with valid link.

### Task 4.5 — Build host application confirmation email

**File**: `backend/services/email.js` + `backend/routes/host.js`

`sendHostConfirmationEmail(user, application)`: HTML with org name, city, submission date, review timeline.
Call from `/host/apply` after DB insert.

**Verify**: Host application submitted → confirmation email received.

---

## Phase 4 Complete Checklist
- [ ] All 4 email types send successfully in development (use SendGrid sandbox mode)
- [ ] No email failure crashes a request
- [ ] QR code renders correctly in Gmail and Apple Mail
- [ ] Reset link expires after 1 hour
- [ ] SENDGRID_API_KEY documented in .env.example

---

# Phase 5 — Spec + Plan + Tasks: Promo Codes & Applications
**Spec Kit artifact**

---

## Spec

### F1 — Server-side Promo Validation
`POST /api/promo/validate` (protected — requires auth).
Accepts `{ code, plan_key }`.
Checks `promo_codes` table: code must exist, `is_active = true`, `used_count < max_uses` (if max_uses not null), `expires_at > NOW()` (if not null).
Returns `{ discount_type, discount_value }` or 400 with reason.
Does NOT increment `used_count` — only validates. Count incremented when order is confirmed.

### F2 — Host Application Endpoint
`POST /api/host/apply` (protected — requires auth).
Accepts: `organization`, `venue_name`, `venue_capacity`, `city`, `country`, `message`.
All fields required except `message`. `venue_capacity` must be a positive integer.
Inserts into `host_applications` with `status = 'pending'`.
Sends confirmation email (Phase 4).
Returns `{ success: true, data: { application_id } }`.

### F3 — Sponsor / General Contact Endpoint
`POST /api/contact` (public — no auth required).
Accepts: `name`, `email`, `type` (sponsor | host | general), `message`.
Validates email format. All fields required.
Inserts into `inquiries` table.
Returns `{ success: true }`.

### F4 — Frontend Promo Integration
Move promo code validation from frontend fake JS to real API call.
On "Apply" click: call `POST /api/promo/validate` with code + current plan_key.
Display server-returned discount in order summary.
Pass validated code to `POST /orders/create`.

### Acceptance Criteria
- [ ] APEX20 code returns 20% discount
- [ ] Expired code returns 400 with reason 'Code has expired'
- [ ] Maxed-out code returns 400 with reason 'Code usage limit reached'
- [ ] Invalid code returns 400 with reason 'Invalid promo code'
- [ ] Host application saves to DB with status 'pending'
- [ ] Contact form saves to inquiries table

---

## Technical Plan

### Promo Validation Query

```sql
SELECT * FROM promo_codes
WHERE code = $1
  AND is_active = true
  AND (max_uses IS NULL OR used_count < max_uses)
  AND (expires_at IS NULL OR expires_at > NOW())
```

### Incrementing used_count

Increment `used_count` in `orderService.createOrderRecord()` after successful payment:

```sql
UPDATE promo_codes SET used_count = used_count + 1 WHERE code = $1
```

---

## Tasks

### Task 5.1 — Build POST /promo/validate

**File**: `backend/routes/promo.js`

Apply `authMiddleware`. Validate `code` (notEmpty, max 50 chars).
Run validation query. Return discount data or specific 400 error messages.

**Verify**: APEX20 → 200 with discount. FAKECODE → 400 'Invalid promo code'.

### Task 5.2 — Integrate promo increment into order confirm

**File**: `backend/services/orderService.js`

In `createOrderRecord()`: if `promo_code` provided, run UPDATE on `promo_codes` table to increment `used_count`.
Wrap in same DB transaction as order insert.

**Verify**: After purchase with APEX20, `used_count` in DB increases by 1.

### Task 5.3 — Build POST /host/apply

**File**: `backend/routes/host.js`

Apply `authMiddleware`. Validate all required fields.
Check if user already has a pending application → 409 if yes.
INSERT into `host_applications`. Call `sendHostConfirmationEmail()`.
Return application id.

**Verify**: Submit valid application → row in DB, email sent, 201 response.

### Task 5.4 — Build POST /contact

**File**: `backend/routes/contact.js`

Public endpoint (no auth). Validate name, email (isEmail), type (isIn(['sponsor','host','general'])), message.
INSERT into `inquiries`.
Return 200.

**Verify**: POST with all fields → row in inquiries table.

### Task 5.5 — Connect frontend promo input to API

**File**: `index.js` (payment modal script)

Replace `applyPromo()` fake function with `fetch('http://localhost:5000/api/promo/validate', { method: 'POST', credentials: 'include', body: JSON.stringify({ code, plan_key }) })`.
On success: apply returned discount to order summary.
On error: show server error message.

**Verify**: Typing APEX20 in promo field → API called → 20% applied to total.

---

## Phase 5 Complete Checklist
- [ ] Promo validation is server-side, not client-side
- [ ] used_count increments after purchase
- [ ] Host application saves and triggers email
- [ ] Contact form saves to inquiries
- [ ] Frontend promo field calls API

---

# Phase 6 — Spec + Plan + Tasks: Admin Dashboard
**Spec Kit artifact**

---

## Spec

### F1 — Dashboard Stats
`GET /api/admin/dashboard` (admin only).
Returns: `{ total_revenue, tickets_sold, total_users, orders_by_status, signups_last_30_days: [{date, count}] }`.

### F2 — Orders Management
`GET /api/admin/orders` (admin only). Supports: `?status=paid&plan=studio&page=1&limit=20`.
`GET /api/admin/orders/:id` — full order detail with tickets.
`POST /api/admin/orders/:id/refund` — calls Stripe refund, updates status.

### F3 — Users Management
`GET /api/admin/users` (admin only). Supports: `?role=attendee&page=1&limit=20`.
`PUT /api/admin/users/:id` — update role (promote to admin, etc.).

### F4 — Content Management
`GET/POST/PUT/DELETE /api/admin/speakers` — full CRUD.
`GET/POST/PUT/DELETE /api/admin/sponsors` — full CRUD.
`GET/POST/PUT/DELETE /api/admin/promo-codes` — full CRUD.

### F5 — Host Applications
`GET /api/admin/host-applications` (admin only). Filter by status.
`PUT /api/admin/host-applications/:id` — update status to 'approved' or 'rejected'.
On approval: send approval email to applicant.

### F6 — Admin Dashboard Page
`/admin` page in Next.js — protected, admin role required.
Shows: 4 stat cards (revenue, tickets, users, orders), orders table with filters, quick action buttons.
Sidebar navigation: Dashboard, Orders, Users, Speakers, Sponsors, Promo Codes, Host Applications.

### Acceptance Criteria
- [ ] Non-admin user gets 403 on all /admin routes
- [ ] Dashboard stats match actual DB data
- [ ] Orders table paginates correctly
- [ ] Approving a host application sends email and updates status
- [ ] Speaker CRUD updates speaker cards on frontend (after revalidation)

---

## Technical Plan

### Stats Query

```sql
SELECT
  COALESCE(SUM(total_amount) FILTER (WHERE status='paid'), 0) AS total_revenue,
  COUNT(*) FILTER (WHERE status='paid') AS paid_orders,
  (SELECT COUNT(*) FROM users WHERE role='attendee') AS total_users,
  (SELECT COUNT(*) FROM tickets) AS tickets_sold
FROM orders;
```

### Pagination Pattern

```js
const page = parseInt(req.query.page) || 1;
const limit = Math.min(parseInt(req.query.limit) || 20, 100);
const offset = (page - 1) * limit;
// Query: ... LIMIT $1 OFFSET $2
// Return: { data, pagination: { page, limit, total, pages } }
```

---

## Tasks

### Task 6.1 — Build GET /admin/dashboard

**File**: `backend/routes/admin.js`

Apply `authMiddleware` + `roleCheck('admin')`.
Run stats aggregation query. Run daily signups query for last 30 days.
Return combined stats object.

### Task 6.2 — Build orders admin endpoints

**File**: `backend/routes/admin.js`

`GET /admin/orders`: dynamic WHERE clause from query params + pagination.
`GET /admin/orders/:id`: join with tickets, user details.
`POST /admin/orders/:id/refund`: `stripe.refunds.create({ payment_intent })` → update status.

### Task 6.3 — Build users admin endpoints

**File**: `backend/routes/admin.js`

`GET /admin/users`: paginated list with filters.
`PUT /admin/users/:id`: validate role value, update user.

### Task 6.4 — Build content CRUD endpoints

**File**: `backend/routes/admin.js`

Speakers: GET all, GET one, POST (create), PUT (update), DELETE (set is_active=false).
Sponsors: same pattern.
Promo codes: same pattern + GET /admin/promo-codes/:id/stats (uses, revenue generated).

### Task 6.5 — Build host applications admin endpoints

**File**: `backend/routes/admin.js`

`GET /admin/host-applications`: filter by status.
`PUT /admin/host-applications/:id`: validate status in ['approved','rejected'].
On approved: send approval email with next steps.

### Task 6.6 — Build admin dashboard HTML page

**File**: `admin.html` + `admin.js`

Create separate admin page with auth check on load — redirect non-admin to home.
Stat cards: fetch from `GET /api/admin/dashboard`.
Orders table: fetch from `GET /api/admin/orders`, client-side filter controls.
Sidebar with links to other admin sections.
Include admin.css for styling.

**Verify**: Admin user can access /admin.html, regular user gets redirected.

---

## Phase 6 Complete Checklist
- [ ] All admin routes return 403 for non-admin users
- [ ] Stats are accurate against DB
- [ ] Pagination works on orders and users
- [ ] Speaker CRUD works end to end
- [ ] Host application approval sends email

---

# Phase 7 — Spec + Plan + Tasks: Static Site Production Deploy
**Spec Kit artifact**

---

## Spec

### F1 — Static Site Optimization
Optimize HTML/JS for production deployment.
Minify CSS and JS. Optimize images.
Add service worker for caching static assets.
Ensure all API calls use production URLs.

### F2 — User Dashboard Integration
Add `/dashboard` section to main HTML or separate page.
Shows: user profile, my orders list with ticket codes, QR code download.
Protected by auth check.

### F3 — Production Backend Hardening
Enforce HTTPS in production (trust proxy for Railway).
Remove all `console.log` — use only `console.error` for exceptions.
Add `X-Request-ID` header to all responses for tracing.
Validate env on startup (Phase 1 pattern).
Ensure database migrations run before server starts.

### F4 — Deploy
Backend to Railway: Node.js service + PostgreSQL add-on.
Frontend to GitHub Pages: static files from repository root.
Custom domain: `apexsummit.org` → GitHub Pages. `api.apexsummit.org` → Railway.
SendGrid domain authentication for email deliverability.
Stripe production keys + webhook endpoint registered.

### Acceptance Criteria
- [ ] All sections render correctly on GitHub Pages
- [ ] Auth modal connects to production API
- [ ] Payment flow works with Stripe production keys
- [ ] Emails deliver to real inboxes
- [ ] SSL on both domains
- [ ] Page loads fast (< 3s)

---

## Technical Plan

### Next.js Project Structure

```
leadership-summit.html
├── index.js               ← auth modal, payment modal scripts
├── admin.html             ← admin dashboard page
├── admin.js               ← admin dashboard script
├── style.css              ← all styles
├── admin.css              ← admin styles
└── assets/
    ├── speakers/          ← speaker images
    └── sponsors/          ← sponsor logos
```

### Railway Deploy Commands

```
# Build command (runs on every deploy)
npm install && node database/migrate.js

# Start command
node server.js

# Environment variables to set in Railway dashboard:
# NODE_ENV=production
# DATABASE_URL (auto-set by Railway PostgreSQL add-on)
# JWT_SECRET, STRIPE_SECRET_KEY, STRIPE_WEBHOOK_SECRET
# SENDGRID_API_KEY, EMAIL_FROM, EMAIL_FROM_NAME
# FRONTEND_URL=https://apexsummit.org
```

---

## Tasks

### Task 7.1 — Add user dashboard to main site

Add dashboard section to `leadership-summit.html` or create `dashboard.html`.
Show user name, order history from `GET /api/orders/me`.
For each order: show plan, date, total, status badge.
For each ticket: show ticket code, QR code image (from API).
Protect with auth check.

### Task 7.2 — Optimize static assets

Minify `style.css` and `index.js` for production.
Optimize speaker/sponsor images (WebP format, lazy loading).
Add service worker for caching static assets.

### Task 7.3 — Update API URLs for production

Replace `http://localhost:5000` with production API URL in all fetch calls.
Add environment variable handling for dev vs prod.

### Task 7.4 — Harden backend for production

Add `app.set('trust proxy', 1)` for Railway.
Add `X-Request-ID` middleware.
Ensure all async handlers have try/catch.
Remove all debug console.logs.
Test with `NODE_ENV=production`.

### Task 7.5 — Deploy backend to Railway

Create Railway project. Add Node.js service from GitHub repo.
Add PostgreSQL add-on — copy `DATABASE_URL`.
Set all env variables in Railway dashboard.
Set build + start commands.
Verify: `https://api.apexsummit.org/health` returns 200.

### Task 7.6 — Deploy frontend to GitHub Pages

Enable GitHub Pages in repository settings.
Set source to main branch, root directory.
Add custom domain `apexsummit.org`.
Verify: all sections load, auth modal works, payment flow completes.

### Task 7.7 — Register Stripe production webhook

In Stripe dashboard → Webhooks → Add endpoint.
URL: `https://api.apexsummit.org/api/webhooks/stripe`.
Events: `payment_intent.succeeded`, `payment_intent.payment_failed`, `charge.refunded`.
Copy signing secret → update Railway env var `STRIPE_WEBHOOK_SECRET`.

### Task 7.9 — Run Lighthouse audit + fix issues

Run `lighthouse https://apexsummit.org` for Performance, Accessibility, SEO, Best Practices.
Target ≥ 90 on all four.
Common fixes: add `alt` to all images, check color contrast, compress speaker photos.

---

## Phase 7 Complete Checklist
- [ ] `next build` succeeds with no TypeScript or lint errors
- [ ] Production API at `api.apexsummit.org/health` returns 200
- [ ] Auth flow works end-to-end on production domain
- [ ] Real Stripe purchase completes and receipt email arrives
- [ ] SSL valid on both domains
- [ ] Lighthouse ≥ 90 all categories
- [ ] No `.env` files in git history

---

*Apex Summit Spec Kit Canvas · All 7 phases · May 2026*
