# Phase 1 — Spec: Project Setup & Database
**Spec Kit artifact** · Run `/speckit.specify` then `/speckit.clarify` to refine this document before planning.

---

## Overview

Set up the complete backend project foundation for the Apex Leadership Summit API.
This phase creates the server, database connection, and full schema — everything subsequent phases build on.

---

## Goals

- A running Express server that responds to health checks
- A live PostgreSQL connection validated on startup
- All 8 database tables created and indexed
- Seed data loaded (plans, speakers, sponsors, promo codes)
- A clear folder structure every future phase can extend

---

## Functional Requirements

### F1 — Express Server
The server must start on the port defined in `PORT` env variable (default 5000).
It must respond to `GET /health` with `{ status: "ok", timestamp: "..." }`.
On startup it must validate that all required env variables exist and throw a descriptive error if any are missing.

### F2 — Middleware Stack
The server must use:
- `helmet` for security headers
- `cors` restricted to `FRONTEND_URL` env variable
- `express.json()` for body parsing with 10kb limit
- `morgan` for HTTP request logging
- `cookie-parser` for JWT cookie access
- A global error handler that catches unhandled exceptions and returns safe 500 responses

### F3 — Database Connection
Use `pg.Pool` with config from env variables.
Pool must test the connection on startup with `pool.query('SELECT NOW()')`.
If connection fails, log the error and exit with code 1.
Export the pool for use in all route files.

### F4 — Database Schema
Create all tables in a single `schema.sql` file.
Tables: `users`, `plans`, `orders`, `tickets`, `promo_codes`, `speakers`, `sponsors`, `host_applications`, `inquiries`.
All primary keys are UUID. All tables have `created_at`. Tables with mutable data have `updated_at`.
Schema must be idempotent: use `CREATE TABLE IF NOT EXISTS`.

### F5 — Seed Data
Seed file populates:
- 4 ticket plans: local ($199), studio ($259), online ($199), team ($179)
- 8 speakers with name, role, bio, talk title, talk description, image filename
- 8 sponsors with name, logo filename, website, tier
- 2 promo codes: APEX20 (20% off), SUMMIT10 (10% off), both with max 500 uses
Seed must be idempotent: use `INSERT ... ON CONFLICT DO NOTHING`.

### F6 — Migration & Seed Runners
`database/migrate.js`: connects to DB, runs `schema.sql`, logs success per table.
`database/seed.js`: connects to DB, runs `seed.sql`, logs rows inserted.
Both accept `--force` flag to drop and recreate all tables (dev only, blocked in production).

---

## Out of Scope for This Phase

- Authentication (Phase 2)
- Any route beyond `/health`
- Frontend changes

---

## Acceptance Criteria

- [ ] `npm run dev` starts server without errors
- [ ] `GET /health` returns 200 with JSON body
- [ ] `npm run migrate` creates all 9 tables in PostgreSQL
- [ ] `npm run seed` inserts plans, speakers, sponsors, promo codes
- [ ] Missing env variable causes startup to fail with a named error
- [ ] Bad DB credentials cause startup to fail with a clear message
- [ ] All env variables documented in `.env.example`
