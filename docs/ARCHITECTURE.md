# Architecture

## Frontend choice

The dashboard is **Laravel 12 + Inertia + React + TypeScript**.

A single application keeps session authentication, CSRF, and authorization in one place. A versioned Sanctum API (`/api/v1`) is available for integrations. Redirect pages are standalone Blade views so they never load the dashboard JavaScript bundle.

## Static vs Dynamic

These are separate product concepts, not two skins of the same record.

- **Static QR** encodes the final payload in the image (`https://example.com/page`, `WIFI:...`, vCard, …). Scans never hit this platform. Editing content generates a new image; printed codes stay unchanged.
- **Dynamic QR** encodes only `QR_SHORT_BASE_URL + "/" + slug`. The destination is resolved on our server and can change without regenerating the QR.

`qr_codes.encoded_payload` always stores what is actually inside the QR image.

## Redirect flow

```
GET /r/{slug}  (or host-aware short domain)
  → Redis/cache lookup (qr:redirect:{slug})
  → status / schedule / password / scan-limit checks
  → QrRuleEngine (device, country, language, datetime, A/B)
  → append UTM
  → queue TrackQrScan on the analytics queue
  → HTTP 302 + Cache-Control: no-store
```

Analytics failure must not break the redirect. GeoIP, UA parsing, and aggregates run only in the queued job.

Default redirect status is **302**, never 301, because destinations are editable.

## Caching

`QrRedirectCache` stores a compact slug snapshot. It is written on create/update and forgotten when destination, status, rules, expiration, or scan limits change.

Scan limits use an atomic cache increment (`qr:scans:{id}`), not `COUNT(*)` on every request.

## Privacy

Visitor uniqueness is `HMAC-SHA256(ip + normalized UA, QR_ANALYTICS_HASH_SECRET)`. Raw IPs are not stored unless `QR_ANALYTICS_STORE_RAW_IP=true`. Unique scans are estimates.

## Rule engine

`QrRuleEngine` dispatches to typed handlers. Controllers do not contain redirect conditionals.

## Domains

Never hard-coded. Local development uses `APP_URL` plus `QR_SHORT_BASE_URL=http://localhost:8000/r`. Production can set `QR_SHORT_HOST=q.example.com` for host-aware routing while the dashboard stays on `qr.example.com`.

## SaaS readiness

Workspaces, roles, tags, custom domains, and API token abilities exist in the schema. Billing is not implemented.

## Horizon

Horizon was not installed: it requires `ext-pcntl`, which is unavailable on Windows. Production Linux should run `php artisan queue:work redis --queue=analytics,default` (or Horizon if `pcntl` is present).
