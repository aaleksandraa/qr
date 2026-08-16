# Database

Production database is **PostgreSQL**. Automated tests use SQLite in-memory. JSON columns are used for static payloads, UTM, design, and rule configuration. On PostgreSQL these can be altered to `jsonb` if you need GIN indexes.

## Core tables

```
users
workspaces
workspace_user
campaigns
folders
tags
custom_domains
qr_codes
qr_code_tag
qr_destination_history
qr_redirect_rules
qr_scans
qr_scan_daily_stats
audit_logs
personal_access_tokens
```

## qr_codes

Internal PK is a bigint. Public identifier is a ULID (`public_id`). Redirect slug is a separate unique column and is immutable after creation.

Important columns:

- `qr_type` — `static` | `dynamic`
- `encoded_payload` — exact QR contents
- `static_payload` — structured Static fields
- `destination_url` — Dynamic destination only
- `slug` — UNIQUE
- counters: `total_scans`, `human_scans`, `bot_scans`, `estimated_unique_scans`
- `password_hash` — hashed PIN, never plaintext
- `deleted_at` — soft delete

## Relationships

- Workspace 1—N QR codes, campaigns, folders, tags, custom domains
- Campaign 1—N QR codes
- Folder 1—N QR codes (parent_id reserved for later nesting)
- QR 1—N destination history, rules, scans, daily stats
- QR N—N tags

## Indexes

- `qr_codes.slug` unique
- workspace + type / status / campaign / folder
- `qr_scans (qr_code_id, scanned_at)`
- `qr_scans (qr_code_id, is_bot)`
- `qr_scans (qr_code_id, country_code)`
- `qr_scan_daily_stats (qr_code_id, date)` unique

## Soft deletes

QR codes and campaigns are soft-deleted so analytics history remains. A deleted Dynamic slug shows the controlled not-found page.
