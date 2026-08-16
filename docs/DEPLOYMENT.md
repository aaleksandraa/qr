# Deployment

## Requirements

- PHP 8.2+ (8.3+ recommended)
- Composer
- Node 20+
- PostgreSQL 16
- Redis 7
- Queue worker
- Scheduler cron

## Environment

```env
APP_URL=https://qr.example.com
QR_SHORT_BASE_URL=https://q.example.com
QR_SHORT_HOST=q.example.com
DB_CONNECTION=pgsql
CACHE_STORE=redis
QUEUE_CONNECTION=redis
QR_ANALYTICS_HASH_SECRET=long-random-string
PUBLIC_REGISTRATION=false
```

Both hostnames may point at the same Laravel `public/` directory. Routes distinguish the short host via `QR_SHORT_HOST`. If only one hostname is available, keep `QR_SHORT_BASE_URL=https://qr.example.com/r`.

## Commands

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Queue worker:

```bash
php artisan queue:work redis --queue=analytics,default --tries=3
```

Scheduler crontab:

```text
* * * * * cd /var/www/qr && php artisan schedule:run >> /dev/null 2>&1
```

## Health

- `/up` — Laravel framework health
- `/health` — app + database + cache (no secrets)

## Security checklist

- HTTPS on both hosts
- `QR_ANALYTICS_HASH_SECRET` distinct from day-to-day rotation material
- Do not enable `QR_ANALYTICS_STORE_RAW_IP` unless legally required
- Reject SVG logo uploads until a sanitizer is added
- Rate-limit login and API; do not aggressively rate-limit redirects
- Keep `PUBLIC_REGISTRATION=false` for private deployments
- If public signup is on, set Turnstile keys and keep the built-in honeypot, timing, disposable-email, and register rate limits

## Local Docker

`docker compose up -d` starts PostgreSQL 16 and Redis 7. Docker Desktop must be running.
