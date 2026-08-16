# QR Manager

Self-hosted platform for **Static (Direct)** and **Dynamic (Smart)** QR codes.

Static QR codes encode the final content in the image. Dynamic QR codes encode your short URL, then redirect with HTTP 302 so the destination can change without reprinting.

## Stack

Laravel 12, PHP 8.2+, PostgreSQL, Redis, queues, Sanctum, Inertia, React, TypeScript, Tailwind, shadcn/ui.

The dashboard uses Inertia so authentication stays server-side. `/api/v1` uses Sanctum tokens for integrations. The redirect engine is a thin controller over cache + a rule engine + an async analytics job.

## Requirements

- PHP 8.2+ with `pdo_pgsql`, `pdo_sqlite`, `redis`, `gd`
- Composer, Node 20+, npm
- PostgreSQL + Redis for production (`docker compose up -d` when Docker Desktop is running)

## Installation

```bash
cp .env.example .env
php artisan key:generate
# set QR_ANALYTICS_HASH_SECRET to a long random value
php artisan migrate
php artisan db:seed
npm install
npm run build
php artisan serve
```

Default seeder account: `admin@example.com` / `password`.

Local short URLs: `http://localhost:8000/r/{slug}`.

## Runtime

```bash
composer run dev
# or separately:
php artisan queue:work --queue=analytics,default
php artisan schedule:run
```

## Tests

```bash
php artisan test
```

## Static vs Dynamic

| | Static | Dynamic |
|---|---|---|
| QR payload | Final content | Short URL |
| Change destination | New QR image | Same QR image |
| Platform scan analytics | No | Yes (async) |
| Works if this app is down | Yes | No |

## Important configuration

See `.env.example`. Domains are never hard-coded.

- `APP_URL` — dashboard
- `QR_SHORT_BASE_URL` / `QR_SHORT_HOST` — redirect
- `QR_ANALYTICS_HASH_SECRET` — HMAC visitor hash
- `PUBLIC_REGISTRATION` — off by default; set `true` to show the landing signup form
- `TURNSTILE_SITE_KEY` / `TURNSTILE_SECRET_KEY` — optional Cloudflare Turnstile on register
- Registration is honeypot-protected, timed, rate-limited, and blocks disposable email domains

## Documentation

- `docs/IMPLEMENTATION.md`
- `docs/ARCHITECTURE.md`
- `docs/DATABASE.md`
- `docs/DEPLOYMENT.md`
