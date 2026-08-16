# Implementation Checklist

This file tracks real, working implementation — not placeholders.

## Repository analysis (2026-08-15)

**Initial state:** empty repository except `upustvo.md`.

**Initialized as:** Laravel 12 + Inertia + React + TypeScript + Tailwind + shadcn/ui, plus Sanctum `/api/v1`.

**Local environment notes:**

- PHP 8.2.12 is installed (spec prefers 8.3+; Laravel 12 requires `^8.2`).
- Docker Desktop was not running during implementation; `docker-compose.yml` is ready for PostgreSQL 16 + Redis 7.
- Tests use SQLite in-memory. Local `.env` currently uses SQLite so the app runs without Docker.
- Horizon was not installed: it requires `ext-pcntl` (Unix). Use `queue:work` instead.

---

## Phase 1 — Foundation

- [x] Laravel 12 project
- [x] React + TypeScript + Inertia + Tailwind + shadcn/ui
- [x] PostgreSQL-oriented schema (SQLite for automated tests / local fallback)
- [x] Redis cache / queue configuration (`.env.example` + docker-compose)
- [x] Authentication (login, logout, forgot/reset password)
- [x] Public registration gated by `PUBLIC_REGISTRATION`
- [x] Landing page signup + spam protection (honeypot, timing, rate limits, disposable email, optional Turnstile)
- [x] Workspace foundation + default workspace
- [x] Domain enums
- [x] Core migrations
- [x] Dashboard shell + navigation
- [x] Config-driven domains (`APP_URL`, `QR_SHORT_BASE_URL`, `QR_SHORT_HOST`)
- [x] Health endpoint (`/health` and Laravel `/up`)
- [x] `.env.example`

## Phase 2 — Static QR

- [x] Static QR model + payload builders
- [x] URL / Text / Email / Phone / SMS / Wi-Fi / vCard / Location
- [x] QR image generation (SVG + PNG)
- [x] Design options + scanability safeguards
- [x] Create / list / detail / download
- [x] Tests (payload is destination, not redirect URL)

## Phase 3 — Dynamic QR core

- [x] Dynamic QR creation + slug generation
- [x] Custom aliases + reserved slugs + UNIQUE constraint
- [x] Local `/r/{slug}` routing + optional short host
- [x] Redirect resolver + cache
- [x] HTTP 302 + `Cache-Control: no-store`
- [x] Edit destination without regenerating QR
- [x] Destination history
- [x] Pause / archive / expiration
- [x] Tests (302 destination change, same slug)

## Phase 4 — Analytics

- [x] Async scan job (redirect never waits; `Bus::fake` coverage)
- [x] Privacy-first visitor hash (no raw IP by default)
- [x] Human / bot / estimated unique
- [x] Device / OS / browser / header geo
- [x] Daily aggregates
- [x] Analytics dashboard
- [x] Tests

## Phase 5 — Organization

- [x] Campaigns + campaign analytics
- [x] Folders
- [x] Tags (schema + attach)
- [x] Filtering + search

## Phase 6 — Advanced Dynamic features

- [x] UTM parameters
- [x] Scan limit + fallback URL
- [x] Password / PIN (hashed, session unlock page)
- [x] Device / country / language / date-time / A/B rules
- [x] Modular `QrRuleEngine`

## Phase 7 — Product hardening

- [x] Audit log
- [x] API tokens + scopes
- [x] Custom domain foundation (schema, not DNS automation)
- [x] Security headers / rate limiting
- [x] Production documentation
- [x] Full test suite green (`50 passed`)

---

## Architectural decisions

- Inertia dashboard + Sanctum API (see `docs/ARCHITECTURE.md`).
- Slugs are globally unique and immutable after create.
- SVG logo uploads are rejected until sanitization exists. Generated QR SVG is server-side only.
- Horizon deferred on Windows; Redis queues are configured.
- `json` columns instead of PostgreSQL-only `jsonb` so SQLite tests run.

## Intentionally not in this delivery

- Billing / SaaS plans
- Webhooks
- CSV analytics export
- Custom-domain DNS/TLS automation
- Full `bs`/`en` UI translation files (locale config exists; dashboard copy is English)
- QR decode-from-image acceptance beyond payload + SVG/PNG generation tests
