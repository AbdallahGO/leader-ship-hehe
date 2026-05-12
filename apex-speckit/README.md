# Apex Summit — Spec Kit Canvas

This folder contains all Spec-Driven Development artifacts for the Apex Leadership Summit 2026 backend and deployment. Use these files with GitHub Spec Kit and any supported AI coding agent (Claude Code, GitHub Copilot, Gemini CLI).

---

## How to Use These Files

### 1. Install Spec Kit

```bash
# Install the specify CLI
uv tool install specify-cli --from git+https://github.com/github/spec-kit.git

# Initialize in your project root
specify init apex-summit

# Copy the .specify folder from this canvas into your project
cp -r .specify/ your-project/.specify/
cp constitution.md your-project/constitution.md
```

### 2. Work through phases in order

Each phase has three artifacts in `.specify/features/phase-N-name/`:
- `spec.md` — what to build (functional requirements)
- `plan.md` — how to build it (technical decisions)
- `tasks.md` — step-by-step implementation checklist

### 3. Spec Kit command sequence per phase

```bash
# The AI reads the spec and asks clarifying questions
/speckit.clarify

# The AI generates or validates the technical plan
/speckit.plan

# The AI breaks down into granular tasks
/speckit.tasks

# The AI implements task by task
/speckit.implement
```

### 4. Phase order (do not skip)

| Phase | Folder | Duration |
|---|---|---|
| 1 — Setup & Database | `phase-1-setup/` | ~1 week |
| 2 — Authentication | `phase-2-auth/` | ~1 week |
| 3 — Stripe Payments | `phase-3-payments/` | ~1 week |
| 4 — Email | `phases-4-to-7/` (section 4) | ~3 days |
| 5 — Promo & Applications | `phases-4-to-7/` (section 5) | ~3 days |
| 6 — Admin Dashboard | `phases-4-to-7/` (section 6) | ~1 week |
| 7 — Next.js & Deploy | `phases-4-to-7/` (section 7) | ~1 week |

---

## Constitution Rules (AI must read first)

The `constitution.md` file in the root defines non-negotiable rules:
- All passwords hashed with bcrypt (12 rounds)
- JWT in httpOnly cookies only
- All SQL uses parameterized queries
- UUIDs for all primary keys
- No secrets in code — env vars only
- Admin routes always require both auth + role check

**The AI agent must read `constitution.md` before starting any phase.**

---

## File Structure

```
apex-speckit/
├── constitution.md                          ← READ FIRST — project rules
├── README.md                                ← this file
└── .specify/
    └── features/
        ├── phase-1-setup/
        │   ├── spec.md                      ← what to build
        │   ├── plan.md                      ← how to build it
        │   └── tasks.md                     ← implementation checklist
        ├── phase-2-auth/
        │   └── spec-plan-tasks.md           ← all three combined
        ├── phase-3-payments/
        │   └── spec-plan-tasks.md
        └── phases-4-to-7/
            └── spec-plan-tasks.md           ← phases 4, 5, 6, 7
```

---

## Tech Stack Summary

| Layer | Technology |
|---|---|
| Backend | Node.js 20 + Express 4 |
| Database | PostgreSQL 15 |
| ORM / Driver | `pg` (node-postgres) — raw SQL |
| Auth | JWT + bcryptjs |
| Payments | Stripe |
| Email | SendGrid |
| QR Codes | `qrcode` npm |
| Frontend | HTML/CSS/JS (static site) |
| Deploy — Backend | Railway (Node.js + managed PostgreSQL) |
| Deploy — Frontend | GitHub Pages |
| AI Tooling | GitHub Spec Kit v0.8+ |
