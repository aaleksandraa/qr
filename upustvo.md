# MASTER PROJECT PROMPT — Professional Static & Dynamic QR Management Platform

You are acting as the senior software architect, senior Laravel engineer, senior React/TypeScript engineer, database architect, security engineer, and product engineer for this project.

Your task is to design and implement a production-ready QR Code Management Platform.

This must NOT be a simple "generate QR image" demo.

The application must support two fundamentally different QR code types:

1. **Static QR Codes**
2. **Dynamic QR Codes**

The system should behave as a professional self-hosted alternative to services such as QR Code Generator, Bitly QR, Beaconstac, Flowcode, etc., while keeping full ownership of the domain, database, redirects, QR images, and analytics.

The system should initially work as a private/internal platform, but its architecture must make it possible to evolve later into a multi-user SaaS without rewriting the core.

---

# 1. CORE PRODUCT CONCEPT

There are two types of QR codes.

## STATIC QR

A Static QR stores the final payload directly inside the QR image.

Example:

```text
QR
 ↓
https://example.com/page
```

The QR itself contains:

```text
https://example.com/page
```

There is no redirect through our server.

Advantages:

- no dependency on our redirect server
- QR can continue working indefinitely
- ideal for permanent information
- works even if the QR platform is later unavailable
- simpler and faster

Disadvantages:

- destination cannot be changed after printing
- our platform cannot directly track scans
- changing content requires generating a new QR image

Static QR must support multiple content types, not only URLs.

---

## DYNAMIC QR

A Dynamic QR stores our own short URL.

Example:

```text
QR
 ↓
https://q.example.com/a8K2xP
 ↓
our redirect engine
 ↓
analytics event
 ↓
HTTP 302 redirect
 ↓
https://destination.com/page
```

The destination can later be changed without changing or reprinting the QR.

Dynamic QR advantages:

- editable destination
- scan analytics
- unique scan estimation
- device statistics
- country statistics
- campaigns
- UTM tracking
- expiration
- redirect rules
- A/B testing
- custom short links
- possible custom domains

---

# 2. DEFAULT APPLICATION DOMAINS

Never hard-code domains.

Use environment configuration.

Example:

```env
APP_URL=https://qr.example.com
QR_SHORT_BASE_URL=https://q.example.com
```

Conceptually:

```text
qr.example.com
```

is the dashboard/admin application.

And:

```text
q.example.com
```

is the high-performance redirect domain.

For local development allow something like:

```env
APP_URL=http://localhost:8000
QR_SHORT_BASE_URL=http://localhost:8000/r
```

The application must work locally even when a dedicated short-link domain is unavailable.

---

# 3. TECHNOLOGY STACK

Use a modern production-ready stack.

Backend:

```text
Laravel 12
PHP 8.3+
PostgreSQL
Redis
Laravel Queue
Laravel Horizon where appropriate
Laravel Sanctum for API authentication
```

Frontend:

```text
React
TypeScript
Tailwind CSS
shadcn/ui
```

Use Laravel as the primary backend.

A single repository is preferred unless there is a strong architectural reason to split frontend/backend.

A Laravel + React architecture is preferred.

Possible approaches:

```text
Laravel + Inertia + React
```

or a clean Laravel API + React frontend.

Choose the approach that gives the best maintainability for this project.

Explain the choice briefly in the README.

Do NOT introduce unnecessary microservices.

The redirect engine must remain very lightweight.

---

# 4. IMPORTANT DEVELOPMENT RULE

Before modifying code:

1. inspect the existing repository
2. inspect package.json
3. inspect composer.json
4. inspect routes
5. inspect database migrations
6. inspect existing coding conventions
7. inspect existing authentication
8. inspect existing frontend structure

If the repository already contains a project, adapt to it instead of blindly replacing it.

Do not destroy existing working functionality.

If this is an empty repository, initialize the project correctly.

---

# 5. DEVELOPMENT METHODOLOGY

Work in logical phases.

Do NOT only create a plan and stop.

Actually implement the project.

Maintain a checklist in:

```text
docs/IMPLEMENTATION.md
```

Use states:

```text
[ ]
[x]
```

Do not mark functionality complete unless it actually works.

If an advanced feature cannot be completed immediately, keep the architecture prepared for it and document what remains.

Never fake implementation with placeholder buttons that do nothing.

---

# 6. APPLICATION STRUCTURE

Main navigation:

```text
Dashboard

QR Codes
    All QR Codes
    Static QR
    Dynamic QR

Campaigns

Analytics

Folders

Settings
```

Future-ready sections may include:

```text
Custom Domains
API
Team
Billing
```

but billing is NOT required in the first implementation.

---

# 7. CREATE QR WORKFLOW

Clicking:

```text
+ Create QR Code
```

should first display two large options.

## Option 1

```text
STATIC QR
Direct QR

Content is stored directly in the QR code.

✓ No redirect server required
✓ Permanent
✓ Best for permanent data

✕ Destination cannot be edited after printing
✕ No internal scan analytics
```

## Option 2

```text
DYNAMIC QR
Smart QR

QR points to our redirect server.

✓ Change destination anytime
✓ Scan analytics
✓ Campaign tracking
✓ Device/country analytics
✓ Advanced redirect rules
```

The UI must clearly explain the difference.

A user must not accidentally believe a Static QR can later be edited.

---

# 8. STATIC QR CONTENT TYPES

Static QR must support:

```text
URL
Text
Email
Phone
SMS
Wi-Fi
vCard / Contact
Location
```

Design the code so additional static content types can easily be added later.

Use a strategy/service approach rather than putting all serialization logic inside a controller.

Example:

```text
StaticQrPayloadBuilder
```

with dedicated handlers.

---

# 9. STATIC URL QR

Input:

```text
https://example.com/page
```

QR payload:

```text
https://example.com/page
```

Validate:

- scheme must be http or https
- URL must be syntactically valid
- reject javascript:
- reject data:
- reject file:
- reject malformed URLs

Optional feature:

```text
Add UTM parameters
```

Fields:

```text
utm_source
utm_medium
utm_campaign
utm_content
utm_term
```

Example:

```text
https://example.com/academy
?utm_source=qr
&utm_medium=flyer
&utm_campaign=academy_2026
```

Explain clearly:

UTM parameters allow analytics on the destination website, but our QR platform still cannot directly detect a Static QR scan.

---

# 10. STATIC TEXT QR

Allow arbitrary plain text.

Apply a reasonable maximum length.

Display a warning that larger payloads make QR codes denser and harder to scan.

---

# 11. STATIC PHONE QR

Input:

```text
+38765123456
```

Generate a standard telephone payload such as:

```text
tel:+38765123456
```

Normalize safely while preserving international format.

---

# 12. STATIC EMAIL QR

Fields:

```text
Email
Subject
Message
```

Generate proper encoded mailto payload.

Example concept:

```text
mailto:info@example.com?subject=...
```

Correctly URL encode subject/body.

---

# 13. STATIC SMS QR

Fields:

```text
Phone number
Message
```

Generate a standards-compatible mobile SMS payload.

Keep payload serialization in its own service.

---

# 14. STATIC WI-FI QR

Fields:

```text
SSID
Security
Password
Hidden Network
```

Supported security:

```text
WPA/WPA2/WPA3 where QR standard/device compatibility permits
WEP
None
```

Generate the standard Wi-Fi QR payload.

Example concept:

```text
WIFI:T:WPA;S:OfficeWiFi;P:Password123;;
```

Correctly escape special characters.

Never log Wi-Fi passwords.

Treat them as sensitive application data.

Do not expose Wi-Fi credentials in analytics.

---

# 15. STATIC VCARD QR

Fields:

```text
First name
Last name
Company
Job title
Phone
Mobile
Email
Website
Street
City
Postal code
Country
Note
```

Generate a standards-compatible vCard.

Prefer vCard 3.0 for broad device compatibility unless a better reason exists.

Example:

```text
BEGIN:VCARD
VERSION:3.0
...
END:VCARD
```

---

# 16. STATIC LOCATION QR

Allow either:

```text
Latitude
Longitude
```

or an address that can be transformed into a Maps URL.

Do not require a third-party geocoding service for MVP.

Coordinate mode should generate an appropriate geo URI or URL.

---

# 17. DYNAMIC QR MODEL

Dynamic QR should have:

```text
Name
Destination URL
Short slug
Status
Campaign
Folder
Description
Expiration
Scan limit
Tracking enabled
UTM settings
Design settings
Advanced redirect rules
```

The actual QR payload must NOT be the destination.

It must be:

```text
QR_SHORT_BASE_URL + "/" + slug
```

Example:

```text
https://q.example.com/CN26A
```

---

# 18. DYNAMIC SLUGS

Support two slug types.

## Automatically generated

Use a compact collision-resistant Base62-like slug.

Example:

```text
a82Kd9
```

Do not expose sequential database IDs.

Avoid predictable:

```text
1
2
3
4
```

## Custom aliases

Example:

```text
academy
summer-sale
event-2026
```

Rules:

- unique
- safe characters only
- configurable min/max length
- case behavior must be explicitly defined
- reserved application names are prohibited

Reserved examples:

```text
admin
api
login
register
dashboard
analytics
settings
qr
health
robots.txt
favicon.ico
```

Use a database UNIQUE constraint.

Application-level checking alone is not sufficient.

---

# 19. REDIRECT ENGINE

Implement the redirect route separately from dashboard logic.

Concept:

```php
GET /{slug}
```

on the short domain.

The redirect handler should:

1. validate slug
2. locate dynamic QR
3. verify QR status
4. verify expiration
5. verify scan limit
6. evaluate redirect rules
7. dispatch analytics event asynchronously
8. return redirect immediately

Use:

```text
HTTP 302
```

as the default dynamic redirect.

Do NOT use HTTP 301 by default because destinations are editable.

Support configuration if 307 is later preferred.

Add appropriate headers to prevent browsers/CDNs from incorrectly caching editable redirect destinations.

Example concept:

```text
Cache-Control: no-store
```

---

# 20. REDIRECT PERFORMANCE

This route is performance-critical.

Target architecture:

```text
request
 ↓
Redis cache lookup
 ↓
rule resolution
 ↓
queue analytics event
 ↓
302 response
```

Do NOT perform expensive analytics queries before redirecting.

Do NOT calculate dashboard statistics synchronously during redirect.

Do NOT call remote GeoIP APIs synchronously during redirect.

Cache slug-to-destination information in Redis.

Suggested key:

```text
qr:redirect:{slug}
```

Cache invalidation must happen automatically whenever:

- destination changes
- QR is disabled
- expiration changes
- rules change
- scan limits change

---

# 21. REDIRECT CACHE

Create a dedicated service such as:

```text
QrRedirectResolver
QrRedirectCache
```

The controller should remain thin.

Example architecture:

```text
RedirectController
    ↓
QrRedirectResolver
    ↓
QrRuleEngine
    ↓
QrAnalyticsDispatcher
```

Avoid business logic inside controllers.

---

# 22. DYNAMIC QR DESTINATION EDITING

A user must be able to edit:

```text
https://old-domain.com/page
```

to:

```text
https://new-domain.com/new-page
```

without changing:

```text
https://q.example.com/a82Kd9
```

and without regenerating the physical QR.

The QR image should remain the same because the short URL is unchanged.

Show a history of destination changes if practical.

Suggested table:

```text
qr_destination_history
```

Fields:

```text
id
qr_code_id
old_url
new_url
changed_by
created_at
```

---

# 23. STATUS

Support:

```text
active
paused
archived
```

Paused QR should display a clean controlled page rather than returning an ugly server error.

Example:

```text
This QR code is currently unavailable.
```

Archived codes must not disappear from analytics/history.

---

# 24. EXPIRATION

Dynamic QR may optionally have:

```text
starts_at
expires_at
```

Before `starts_at`:

show a configurable not-active page or fallback destination.

After `expires_at`:

either:

```text
show expired page
```

or:

```text
redirect to fallback URL
```

Make this configurable per QR if practical.

---

# 25. SCAN LIMIT

Optional:

```text
maximum scans
```

Examples:

```text
1
100
1000
```

When limit is reached:

- stop normal redirect
- show controlled page or fallback
- do not race incorrectly under concurrent scans

Use an atomic strategy where necessary.

Do not rely on:

```text
SELECT count(*)
```

on every redirect.

Use counters/Redis or database atomic increment logic.

---

# 26. QR PASSWORD / PIN

Prepare architecture for password-protected Dynamic QR codes.

If enabled:

```text
QR
 ↓
short URL
 ↓
minimal PIN page
 ↓
correct PIN
 ↓
destination
```

Passwords/PINs must never be stored in plaintext.

Use secure hashing.

Do not place the password in query parameters.

This can be Phase 2 if needed, but database/model architecture should support it.

---

# 27. CAMPAIGNS

Create a campaign model.

Example:

```text
Crystal Nails Academy 2026
```

Possible QR codes:

```text
CN-FLYER
CN-STORE
CN-EVENT
CN-BILLBOARD
CN-PACKAGE
```

All can point to the same destination while being tracked separately.

Campaign fields:

```text
id
name
description
starts_at
ends_at
status
created_at
updated_at
```

Dashboard should aggregate scans by campaign.

---

# 28. FOLDERS

Support folders to organize QR codes.

Example:

```text
Clients
    Crystal Nails
    WizMedik
    BNCShop
```

Do not make folder hierarchy excessively complex initially.

One-level folders are acceptable for MVP, but model the structure cleanly.

---

# 29. TAGS

Prepare tags such as:

```text
print
event
product
marketing
internal
```

Use a many-to-many relationship.

Tags can be Phase 2 but schema should be designed correctly.

---

# 30. QR DESIGN SYSTEM

Both Static and Dynamic QR codes should support customizable design.

Fields/features:

```text
foreground color
background color
error correction level
margin / quiet zone
module style
finder style where library permits
logo
logo size
frame
optional CTA text
```

Do not prioritize visual gimmicks over scan reliability.

---

# 31. ERROR CORRECTION

Support:

```text
L
M
Q
H
```

Explain these in UI.

Recommended defaults:

```text
M
```

Without logo.

When a significant center logo is enabled, recommend:

```text
H
```

Do not automatically allow a huge logo that makes QR unreadable.

---

# 32. QR SCANABILITY SAFETY

Implement safeguards.

For example:

- warn about insufficient foreground/background contrast
- enforce quiet zone
- prevent extremely large logos
- prevent transparent foreground
- warn when payload becomes very dense
- ensure exported QR is valid

If practical, create an automated QR decode validation test for generated QR images.

The system should be able to confirm:

```text
generated QR → decoded payload === expected payload
```

where technically feasible.

---

# 33. QR EXPORT

Allow:

```text
PNG
SVG
```

PDF export can be Phase 2.

SVG is particularly important for professional printing.

Provide export sizes for PNG, for example:

```text
512 × 512
1024 × 1024
2048 × 2048
```

Do not rasterize SVG unnecessarily.

File names should be meaningful.

Example:

```text
cn-academy-dynamic.svg
```

---

# 34. LOGO STORAGE

Store uploaded QR logos in application storage.

Validate:

- MIME type
- extension
- image size
- dimensions

Do not trust client-side validation.

Prefer:

```text
PNG
JPEG
WEBP
SVG only if safely sanitized
```

If SVG sanitization is not implemented, reject uploaded SVG initially.

---

# 35. ANALYTICS

Analytics only applies directly to Dynamic QR codes.

Main metrics:

```text
Total scans
Estimated unique scans
Human scans
Bot requests
Scans today
Scans this week
Scans this month
Last scan
```

Breakdowns:

```text
Country
Region where available
City where reliably available
Device type
Operating system
Browser
Referrer
Time/day
QR code
Campaign
```

Do not claim GPS-level location.

Display wording such as:

```text
Approximate location based on IP
```

---

# 36. QR SCAN TABLE

Create something similar to:

```text
qr_scans
```

Suggested fields:

```text
id
qr_code_id
scanned_at

visitor_hash

country_code
country_name
region
city

device_type
os
browser

referrer
user_agent_summary

is_bot

request_id

created_at
```

Do not persist raw IP addresses by default.

---

# 37. PRIVACY-FIRST VISITOR IDENTIFICATION

For approximate uniqueness, derive a one-way visitor identifier.

For example using HMAC:

```text
HMAC_SHA256(
    ip_address + normalized_user_agent,
    ANALYTICS_HASH_SECRET
)
```

Never use plain SHA without a secret.

Do not expose this hash to normal UI users.

Document that:

```text
"unique scans" are estimates
```

because multiple people can share a network/device and one person can change networks/devices.

Provide a configurable analytics retention period.

Example:

```env
QR_ANALYTICS_RETENTION_DAYS=365
```

---

# 38. RAW IP PRIVACY

Do not store raw IP in the database by default.

If temporary IP processing is required for GeoIP:

- keep processing minimal
- do not write raw IP to application logs
- do not persist it after processing
- document the behavior

Where infrastructure provides country headers, use them where appropriate.

Create a GeoResolver abstraction so the application is not locked to one provider.

Example:

```text
GeoResolverInterface
```

Possible implementations:

```text
CloudflareGeoResolver
MaxMindGeoResolver
NullGeoResolver
```

Do not make remote API calls during redirect.

---

# 39. DEVICE DETECTION

Parse User-Agent asynchronously.

Store normalized values such as:

```text
Mobile
Desktop
Tablet
Other
```

Operating systems:

```text
iOS
Android
Windows
macOS
Linux
Other
```

Browsers:

```text
Safari
Chrome
Firefox
Edge
Samsung Internet
Other
```

Keep the raw User-Agent only if explicitly required.

Prefer a normalized representation.

---

# 40. BOT DETECTION

Do not count all requests as human scans.

Examples of preview bots/crawlers:

```text
facebookexternalhit
WhatsApp
Slackbot
Googlebot
bingbot
Twitterbot
Discordbot
TelegramBot
```

Implement a bot-detection service.

Store:

```text
is_bot
```

Analytics should separately show:

```text
Human scans
Bot requests
```

Do not silently mix them.

Bot detection should be extensible and not just a massive if statement in the controller.

---

# 41. ANALYTICS QUEUE

A QR scan should dispatch a lightweight event/job.

Concept:

```text
QR request
 ↓
resolve redirect
 ↓
dispatch TrackQrScan job
 ↓
redirect immediately

background:
TrackQrScan
 ↓
normalize device
 ↓
resolve geo
 ↓
detect bot
 ↓
insert analytics row
 ↓
update aggregate counters
```

Use Redis queues.

Provide queue names where useful:

```text
default
analytics
```

---

# 42. ANALYTICS AGGREGATION

Do not calculate expensive analytics by scanning millions of raw records on every dashboard load.

For MVP normal indexed queries are acceptable.

Prepare for aggregate tables/counters.

Possible aggregate table:

```text
qr_scan_daily_stats
```

Fields:

```text
date
qr_code_id
total_scans
human_scans
bot_scans
unique_scans
```

Implement when useful.

---

# 43. DASHBOARD

Create a polished professional dashboard.

Top cards:

```text
Total QR Codes
Dynamic QR Codes
Static QR Codes
Scans Today
Scans This Month
Active Campaigns
```

Charts:

```text
Scans over time
Static vs Dynamic QR count
Top Dynamic QR Codes
Top Campaigns
Device breakdown
Country breakdown
```

Do NOT show fake analytics for Static QR codes.

---

# 44. QR LIST PAGE

Columns:

```text
QR preview
Name
Type
Content/Destination
Short URL if dynamic
Campaign
Status
Scans
Last scanned
Created
Actions
```

Filters:

```text
Static/Dynamic
Status
Campaign
Folder
Created date
```

Search:

```text
Name
Slug
Destination
```

Actions:

```text
View
Edit
Download
Duplicate
Pause
Archive
Delete
```

Deleting should generally be soft-delete where preserving analytics/history is important.

---

# 45. STATIC QR DETAIL PAGE

Show:

```text
QR preview
QR type
Content type
Encoded payload
Design
Created date
Download buttons
```

Display an important warning:

```text
This is a Static QR code.

The content is embedded directly in the QR image.
Changing the content requires generating a new QR code.
```

Do NOT present an "edit destination" button as if existing printed codes will change.

If content is modified, clearly say:

```text
A new QR image will be generated.
Previously downloaded/printed QR codes will remain unchanged.
```

---

# 46. DYNAMIC QR DETAIL PAGE

Show:

```text
QR preview
Name
Short URL
Current destination
Status
Campaign
Created
Last modified
```

Actions:

```text
Edit Destination
Copy Short URL
Download PNG
Download SVG
Pause
Archive
Duplicate
```

Analytics:

```text
Total scans
Estimated unique
Humans
Bots
Today
Last scan
```

Charts underneath.

---

# 47. STATIC → DYNAMIC CONVERSION

Allow a helper action:

```text
Convert to Dynamic QR
```

But it MUST clearly explain:

A Static QR already printed cannot be magically converted.

Conversion means:

1. copy current payload/destination
2. create a new Dynamic QR
3. generate a new QR image

Display warning:

```text
A new QR image will be generated.
Previously printed Static QR codes will not change.
```

---

# 48. DYNAMIC → STATIC

Likewise, converting to Static means creating a new QR image containing the current destination directly.

Never imply that previously printed QR codes are changed.

---

# 49. ADVANCED REDIRECT RULES

Design a redirect rule engine.

Do not hard-code business rules inside the controller.

Suggested model:

```text
qr_redirect_rules
```

Fields:

```text
id
qr_code_id
type
operator
configuration JSONB
destination_url
priority
is_active
created_at
updated_at
```

Rules should be ordered by priority.

Fallback:

```text
qr_codes.destination_url
```

---

# 50. DEVICE REDIRECT RULE

Example:

```text
If device is iOS:
    App Store

If device is Android:
    Google Play

Else:
    Website
```

Architecture:

```text
QrRuleEngine
```

evaluates available conditions.

---

# 51. COUNTRY REDIRECT RULE

Example:

```text
Bosnia and Herzegovina
→ example.ba

Serbia
→ example.rs

Croatia
→ example.hr

Fallback
→ example.com
```

Use country codes internally where possible.

---

# 52. LANGUAGE REDIRECT RULE

Use browser Accept-Language where appropriate.

Example:

```text
de → /de
en → /en
bs → /ba
```

Do not rely on language detection as a security mechanism.

---

# 53. DATE/TIME RULES

Example:

Before a campaign:

```text
/coming-soon
```

During campaign:

```text
/sale
```

After campaign:

```text
/waiting-list
```

All date/time comparisons must use a consistent timezone strategy.

Store timestamps in UTC.

Render them according to configured application/user timezone.

---

# 54. A/B TESTING

Prepare a weighted destination rule.

Example:

```text
Variant A: 50%
Variant B: 50%
```

Where possible, use deterministic assignment based on visitor hash so a visitor does not constantly switch variants.

Store variant analytics.

Support future conversion tracking architecture but actual conversion tracking does not need to be in MVP.

---

# 55. UTM FOR DYNAMIC QR

Dynamic QR should optionally append UTM parameters to its resolved destination.

Fields:

```text
utm_source
utm_medium
utm_campaign
utm_content
utm_term
```

Be careful when destination already has query parameters.

Use a proper URL/query builder.

Do not concatenate query strings incorrectly.

---

# 56. URL VALIDATION AND SECURITY

All redirect destinations must use allowed protocols.

Default:

```text
http
https
```

Reject:

```text
javascript:
data:
file:
ftp:
```

Design optional:

```text
domain allowlist
domain blocklist
```

If this platform later becomes public SaaS, malicious redirect abuse can damage the reputation of the entire short domain.

Prepare an abuse/security architecture.

---

# 57. RATE LIMITING

Apply sensible rate limiting to:

```text
login
API
QR creation
QR edits
authentication endpoints
password attempts
```

Do not aggressively rate-limit legitimate QR redirects.

Redirect abuse protection should be designed separately.

---

# 58. AUTHENTICATION

Implement secure authentication.

Support:

```text
login
logout
forgot password
reset password
```

Do not implement public registration unless enabled via configuration.

Example:

```env
PUBLIC_REGISTRATION=false
```

---

# 59. USER ROLES

Initially support:

```text
admin
user
```

Prepare role architecture so future SaaS may support:

```text
owner
admin
member
viewer
```

Do not overengineer permission complexity in MVP.

---

# 60. WORKSPACE / TENANT READINESS

The application starts as an internal platform but should be structurally ready for multiple organizations later.

Prefer introducing a concept such as:

```text
workspace
```

QR codes, campaigns and folders belong to a workspace.

A default workspace can be created automatically.

Suggested:

```text
workspaces
workspace_user
```

Do not implement complicated subscription tenancy yet.

But avoid writing every query in a way that assumes there will forever be only one owner.

---

# 61. DATABASE DESIGN

Create clean normalized migrations.

Recommended core tables:

```text
users
workspaces
workspace_user

qr_codes
qr_destination_history
qr_scans

campaigns
folders

tags
qr_code_tag

qr_redirect_rules

qr_scan_daily_stats

custom_domains
```

Not every optional table must be exposed immediately, but migrations/models should reflect the architecture where justified.

---

# 62. QR_CODES TABLE

Design carefully.

Example fields:

```text
id

workspace_id
folder_id nullable
campaign_id nullable
created_by

public_id
name
description

qr_type
content_type

slug nullable
destination_url nullable
static_payload JSONB nullable

status

tracking_enabled

starts_at nullable
expires_at nullable

max_scans nullable

password_hash nullable

fallback_url nullable

utm_parameters JSONB nullable

design_config JSONB

total_scans
human_scans
bot_scans
estimated_unique_scans

last_scanned_at nullable

created_at
updated_at
deleted_at
```

Do not blindly use this exact schema if better normalization is appropriate.

Explain important deviations.

Use enums/value objects where they improve correctness.

---

# 63. STATIC_PAYLOAD

Static data may vary by content type.

For example Wi-Fi contains different fields from vCard.

Using:

```text
JSONB
```

is acceptable for static type-specific structured data.

But also derive the final encoded QR payload through a service.

Never rely only on a client-submitted already-encoded payload.

Server must reconstruct/validate it.

---

# 64. IDENTIFIERS

Use database IDs internally.

Expose a separate non-sequential public identifier when useful.

Examples:

```text
ULID
UUID
```

Short redirect slug is a separate concept and should not be overloaded as the primary model identifier.

---

# 65. INDEXES

Add appropriate indexes.

At minimum consider indexes for:

```text
qr_codes.slug
qr_codes.workspace_id
qr_codes.qr_type
qr_codes.status
qr_codes.campaign_id

qr_scans.qr_code_id
qr_scans.scanned_at
qr_scans.is_bot
qr_scans.country_code
```

Unique:

```text
slug
```

or unique per short domain/workspace depending on architecture.

Indexes should reflect real query patterns.

---

# 66. CUSTOM DOMAINS — FUTURE READY

Prepare support for:

```text
go.client.com/academy
```

instead of:

```text
q.example.com/academy
```

Suggested model:

```text
custom_domains
```

Fields:

```text
workspace_id
domain
verification_token
verified_at
status
created_at
updated_at
```

Actual DNS/TLS automation can remain Phase 3.

Initial implementation may support manual verification/activation.

Do not hard-code only one hostname into slug resolution.

---

# 67. API

Create a versioned API.

Example:

```text
/api/v1
```

Possible endpoints:

```text
GET    /api/v1/qr-codes
POST   /api/v1/qr-codes
GET    /api/v1/qr-codes/{id}
PUT    /api/v1/qr-codes/{id}
DELETE /api/v1/qr-codes/{id}

GET    /api/v1/qr-codes/{id}/analytics

POST   /api/v1/qr-codes/{id}/pause
POST   /api/v1/qr-codes/{id}/activate

GET    /api/v1/campaigns
POST   /api/v1/campaigns
```

Use Resources/DTOs.

Do not return raw Eloquent models indiscriminately.

---

# 68. API CREATE DYNAMIC QR EXAMPLE

Request concept:

```json
{
  "name": "Academy Campaign",
  "type": "dynamic",
  "destination_url": "https://example.com/academy",
  "custom_slug": "academy"
}
```

Response concept:

```json
{
  "data": {
    "id": "...",
    "name": "Academy Campaign",
    "type": "dynamic",
    "short_url": "https://q.example.com/academy",
    "destination_url": "https://example.com/academy"
  }
}
```

---

# 69. API CREATE STATIC QR EXAMPLE

Request:

```json
{
  "name": "Website QR",
  "type": "static",
  "content_type": "url",
  "payload": {
    "url": "https://example.com"
  }
}
```

Response:

```json
{
  "data": {
    "id": "...",
    "type": "static",
    "encoded_payload": "https://example.com"
  }
}
```

---

# 70. SANCTUM API TOKENS

Support API tokens so external applications can later generate QR codes automatically.

Permissions/scopes conceptually:

```text
qr:read
qr:create
qr:update
qr:delete
analytics:read
```

If token abilities are implemented, enforce them server-side.

---

# 71. QR GENERATION SERVICE

Create a dedicated service.

Example:

```text
QrImageGenerator
```

Responsibilities:

- accepts normalized payload
- accepts design configuration
- generates SVG
- generates PNG
- applies error correction
- handles logo
- ensures quiet zone
- validates options

Controllers should not contain QR rendering implementation.

Choose a maintained QR library compatible with the installed PHP/Laravel environment.

Do not lock the application architecture to library-specific classes.

Wrap it behind our own service interface.

---

# 72. STATIC PAYLOAD SERVICE

Create something like:

```text
StaticQrPayloadFactory
```

or:

```text
StaticQrPayloadBuilder
```

Example handlers:

```text
UrlPayloadBuilder
TextPayloadBuilder
EmailPayloadBuilder
PhonePayloadBuilder
SmsPayloadBuilder
WifiPayloadBuilder
VCardPayloadBuilder
LocationPayloadBuilder
```

Use clear interfaces.

---

# 73. QR PREVIEW

The create/edit page should show a live preview.

Debounce requests.

Do not regenerate huge raster images on every keystroke.

SVG preview is ideal.

Preview endpoint or frontend generation may be used, but the final downloadable QR must be generated/validated server-side.

---

# 74. UI / UX STYLE

The application should feel like a polished modern SaaS dashboard.

Avoid:

- generic AI-looking gradients everywhere
- excessive glassmorphism
- giant empty cards
- random icons
- unnecessary animations
- visual clutter

Prefer:

- strong typography
- clean spacing
- restrained color palette
- excellent form hierarchy
- clear data tables
- subtle shadows/borders
- excellent dark mode
- mobile responsiveness

Use shadcn/ui patterns intelligently.

---

# 75. CREATE QR WIZARD

Recommended steps.

## Step 1

```text
Choose type

Static
Dynamic
```

## Step 2

For Static:

```text
Choose content type
```

For Dynamic:

```text
Destination URL
Short URL
```

## Step 3

```text
Customize design
```

## Step 4

Dynamic only:

```text
Tracking & campaign
```

## Step 5

```text
Review
Create QR
```

Do not make simple QR creation unnecessarily complicated.

Advanced options should be collapsed by default.

---

# 76. FORM VALIDATION

Implement both:

```text
client-side validation
server-side validation
```

Server-side validation is authoritative.

Display useful validation messages.

Examples:

```text
This short URL is already in use.

Destination must use HTTP or HTTPS.

The QR logo is too large.

Expiration must be after the start date.
```

---

# 77. ANALYTICS UI

Dynamic QR analytics page should include:

Date range:

```text
Today
7 days
30 days
90 days
Custom
```

KPIs:

```text
Total scans
Human scans
Estimated unique
Bots
```

Charts:

```text
Scans over time
Device distribution
OS distribution
Browser distribution
Country distribution
```

Table:

```text
Latest scans
```

Do not expose visitor hash.

---

# 78. CAMPAIGN ANALYTICS

Campaign detail:

```text
Campaign name
Period
Number of QR codes
Total scans
Human scans
Estimated unique
```

Ranking:

```text
QR code        scans
Flyer          1,472
Store            811
Event            729
Billboard        314
```

This is one of the most important marketing features.

---

# 79. DUPLICATE QR

Allow duplication.

Static:

copy content/design into a new Static QR.

Dynamic:

copy destination/design/settings but generate a NEW slug.

Never duplicate a slug.

---

# 80. AUDIT LOG

For important Dynamic QR changes, keep audit history.

At minimum:

```text
destination changed
slug changed if permitted
status changed
expiration changed
redirect rule changed
```

Store:

```text
who
what
old value
new value
when
```

This can use a generic audit log architecture.

---

# 81. SLUG CHANGE SAFETY

Changing an existing Dynamic QR slug is dangerous because printed QR codes point to the old slug.

Prefer NOT allowing slug changes once the QR has been created.

If allowed, the UI must strongly warn the user.

Better:

```text
slug immutable by default
```

Destination remains editable.

This is the safer behavior.

---

# 82. DELETING DYNAMIC QR

Avoid hard-deleting important Dynamic QR records.

Use soft delete/archive.

If a printed QR points to a deleted slug, display a controlled not-found page.

Do not leak internal information.

---

# 83. REDIRECT FAILURE PAGES

Create minimal branded pages for:

```text
QR not found
QR paused
QR expired
QR scan limit reached
QR password required
QR unavailable
```

These pages must be:

- extremely lightweight
- mobile-first
- fast
- accessible

Do not load the entire dashboard JavaScript application.

---

# 84. ACCESSIBILITY

Dashboard should support:

- keyboard navigation
- focus states
- form labels
- adequate contrast
- screen-reader friendly buttons
- accessible charts where practical
- semantic HTML

---

# 85. INTERNATIONALIZATION

Do not hard-code user-facing strings throughout components.

Prepare translation structure.

Initial languages:

```text
bs
en
```

Default may be configured.

Example:

```env
APP_LOCALE=bs
APP_FALLBACK_LOCALE=en
```

Technical/code identifiers stay in English.

---

# 86. TIMEZONE

Store timestamps in UTC.

Use configurable display timezone.

Example:

```env
APP_TIMEZONE=Europe/Sarajevo
```

Be consistent in:

- campaign dates
- QR expiration
- analytics
- dashboard charts

---

# 87. SECURITY HEADERS

Configure sensible headers.

Review:

```text
Content-Security-Policy
X-Content-Type-Options
Referrer-Policy
Permissions-Policy
```

Do not break QR redirects with overly aggressive policies.

---

# 88. CSRF / XSS / SQL INJECTION

Use Laravel's framework protections.

Never:

- trust raw HTML from users
- render unsafe user values
- concatenate SQL
- use unescaped user content
- accept arbitrary redirect schemes

Sanitize any rich content.

Initially avoid rich-text fields unless necessary.

---

# 89. SVG SECURITY

If supporting uploaded SVG logos:

implement proper SVG sanitization.

Otherwise:

```text
do not allow uploaded SVG logos in MVP
```

Generated QR SVG is fine because it is produced by our server.

---

# 90. STORAGE

Use Laravel filesystem abstraction.

Directories conceptually:

```text
qr/logos
qr/exports
```

Do not permanently store every possible exported size unless needed.

Generate exports on demand and cache when useful.

---

# 91. QUEUES

Provide queue configuration.

Recommended queues:

```text
default
analytics
exports
```

Analytics jobs must not block redirect requests.

Include worker/Horizon instructions in README.

---

# 92. SCHEDULER

Use Laravel scheduler for:

```text
analytics retention cleanup
expired campaign maintenance
aggregate statistics
temporary file cleanup
```

Document required cron:

```text
php artisan schedule:run
```

---

# 93. HEALTH CHECK

Add a lightweight health endpoint.

Example:

```text
/health
```

Check at least:

```text
application
database
Redis where appropriate
```

Do not expose sensitive diagnostics publicly.

---

# 94. LOGGING

Use structured application logging.

Never log:

```text
passwords
Wi-Fi passwords
API tokens
raw sensitive payload data unnecessarily
raw IP addresses unless explicitly configured
```

Create useful logs for:

```text
redirect failures
queue failures
QR generation failures
security validation failures
```

---

# 95. OBSERVABILITY

Make the code compatible with future monitoring systems.

Do not tightly couple core logic to a particular SaaS monitoring provider.

Use Laravel's standard exception/reporting mechanisms.

---

# 96. TESTING

Tests are mandatory.

Do not consider the feature complete without meaningful tests.

Use Laravel's testing tools.

Frontend tests can be added for important flows.

---

# 97. STATIC QR TESTS

Test:

```text
URL payload
text payload
email payload
phone payload
SMS payload
Wi-Fi payload
vCard payload
location payload
```

Verify escaping and serialization.

---

# 98. DYNAMIC QR TESTS

Test:

```text
dynamic QR creation
automatic slug creation
custom slug creation
slug collision
invalid slug
reserved slug
destination validation
destination update
```

---

# 99. REDIRECT TESTS

Test:

```text
active QR redirects with 302
paused QR does not redirect
expired QR does not redirect
future QR does not redirect
unknown slug returns controlled page
fallback destination works
UTM parameters work
```

---

# 100. ANALYTICS TESTS

Test:

```text
scan dispatches analytics job
redirect does not wait for analytics processing
bot request classified separately
human scan counted
estimated unique logic
raw IP not stored
```

---

# 101. CACHE TESTS

Test:

```text
redirect result cached
destination update invalidates cache
status update invalidates cache
rule change invalidates cache
```

---

# 102. REDIRECT RULE TESTS

Test:

```text
device rule
country rule
language rule
date rule
fallback
priority
A/B distribution logic
```

where implemented.

---

# 103. AUTHORIZATION TESTS

Ensure:

```text
user cannot access another workspace's QR codes
user cannot edit unauthorized campaigns
user cannot read unauthorized analytics
API token scopes are enforced
```

---

# 104. QR IMAGE TESTS

Where possible test:

```text
payload in generated QR === expected payload
```

Also test:

```text
SVG response
PNG response
correct MIME type
invalid logo rejection
```

---

# 105. SEEDERS

Create useful development seeders.

Example demo data:

```text
Static Website QR
Static Wi-Fi QR
Static vCard QR

Dynamic Academy QR
Dynamic Event QR
Dynamic Product QR

Demo Campaign
```

Include analytics demo data only in development seeder.

Do not pollute production.

---

# 106. README

Create an excellent README.

Include:

```text
Project overview
Architecture
Requirements
Installation
Environment variables
Database setup
Redis setup
Queue setup
Scheduler setup
Frontend setup
Local development
Production deployment
Static vs Dynamic QR explanation
Testing
Security notes
Analytics privacy notes
```

---

# 107. ENVIRONMENT VARIABLES

Document all important configuration.

Conceptually:

```env
APP_NAME="QR Manager"

APP_URL=
QR_SHORT_BASE_URL=

DB_CONNECTION=pgsql

CACHE_STORE=redis
QUEUE_CONNECTION=redis

QR_DEFAULT_ERROR_CORRECTION=M
QR_DEFAULT_FORMAT=svg

QR_ANALYTICS_ENABLED=true
QR_ANALYTICS_RETENTION_DAYS=365
QR_ANALYTICS_HASH_SECRET=

PUBLIC_REGISTRATION=false

APP_LOCALE=bs
APP_FALLBACK_LOCALE=en
APP_TIMEZONE=Europe/Sarajevo
```

Use Laravel defaults where appropriate.

Do not invent duplicate configuration if Laravel already supports it.

---

# 108. PRODUCTION DEPLOYMENT

Document deployment for a standard Linux web server.

Requirements:

```text
PHP
Composer
Node/npm
PostgreSQL
Redis
queue worker
scheduler
```

Both:

```text
qr.example.com
q.example.com
```

may point to the same Laravel `public/` directory.

Laravel routes can differentiate by host when configured.

Provide a fallback architecture if they run on the same hostname.

---

# 109. SHORT DOMAIN ROUTING

Prefer host-aware routing.

Concept:

```php
Route::domain(config('qr.short_domain'))
    ->group(function () {
        Route::get('/{slug}', ...);
    });
```

Do not hard-code hostname in route files.

Handle development mode where both dashboard and redirect use localhost.

---

# 110. PERFORMANCE TARGETS

Redirect endpoint should be optimized for low latency.

Avoid:

```text
N+1 queries
remote requests
complex frontend rendering
analytics aggregation
large session initialization
```

The redirect response should generally require only:

```text
cache lookup
very small amount of logic
queue dispatch
response
```

---

# 111. DATABASE TRANSACTIONS

Use transactions for multi-step operations where partial writes would cause inconsistency.

Examples:

```text
QR creation + related metadata
destination update + history
campaign operations
rule updates
```

---

# 112. SERVICE LAYER

Use clear domain services.

Potential classes:

```text
QrCodeService
StaticQrPayloadBuilder
QrImageGenerator
SlugGenerator
QrRedirectResolver
QrRedirectCache
QrRuleEngine
QrAnalyticsService
QrAnalyticsDispatcher
BotDetector
DeviceDetector
GeoResolver
CampaignAnalyticsService
```

Do not create meaningless services merely to increase abstraction.

Each class should have a clear responsibility.

---

# 113. EVENTS

Consider domain events.

Examples:

```text
QrCodeCreated
QrDestinationChanged
QrCodePaused
QrCodeActivated
QrScanned
```

Use them where they simplify decoupling.

Do not overuse events for simple CRUD.

---

# 114. FRONTEND TYPES

Use proper TypeScript interfaces/types.

Do not use:

```text
any
```

unless absolutely necessary.

Types:

```text
QrCode
QrCodeType
QrContentType
QrDesignConfig
Campaign
QrAnalytics
RedirectRule
```

should correspond cleanly to API resources.

---

# 115. FORM COMPONENTS

Create reusable components.

Examples:

```text
UrlInput
QrDesignEditor
QrPreview
CampaignSelect
FolderSelect
DateRangePicker
UtmEditor
RedirectRuleEditor
QrDownloadMenu
```

Avoid a 1500-line CreateQr component.

---

# 116. ENUMS

Recommended domain enums:

```text
QrCodeType
StaticContentType
QrStatus
RedirectRuleType
DeviceType
CampaignStatus
```

Use PHP backed enums where appropriate.

Keep frontend equivalents synchronized cleanly.

---

# 117. ERROR HANDLING

Use meaningful exceptions.

Examples:

```text
QrNotFound
QrInactive
QrExpired
QrScanLimitReached
InvalidQrDestination
SlugAlreadyExists
```

Translate exceptions into appropriate HTTP responses.

Do not leak stack traces in production.

---

# 118. CONCURRENCY

Consider race conditions for:

```text
custom slug creation
scan limits
aggregate counters
destination updates
```

Use unique constraints and atomic operations.

---

# 119. SOFT DELETES

Use soft delete for:

```text
qr_codes
campaigns
```

where preserving historical analytics is important.

Decide explicitly how redirects behave for deleted QR codes.

---

# 120. API PAGINATION

QR list, scans, campaigns should be paginated.

Support:

```text
page
per_page
search
sort
filters
```

Enforce a maximum `per_page`.

---

# 121. SEARCH

QR search should cover:

```text
name
slug
destination
description
```

Use PostgreSQL indexes appropriately.

Do not introduce Elasticsearch unless scale actually requires it.

---

# 122. EXPORT ANALYTICS

Phase 2 feature:

```text
Export CSV
```

for scan analytics.

Prepare service architecture but do not block core MVP on it.

---

# 123. WEBHOOKS — FUTURE

Future architecture may support events such as:

```text
qr.scanned
qr.scan_limit_reached
qr.expired
```

Do not implement webhooks before core functionality is stable.

---

# 124. SAAS READINESS

Future SaaS could include plans such as:

```text
Free
Pro
Business
```

Limits:

```text
number of dynamic QR codes
monthly scans
analytics retention
custom domains
API access
```

Do NOT implement billing now.

But avoid architectural decisions that make these limits impossible later.

---

# 125. VERY IMPORTANT STATIC/DYNAMIC BUSINESS RULE

Never blur the distinction between Static and Dynamic QR.

Static QR:

```text
encoded payload = final content
```

Dynamic QR:

```text
encoded payload = our short URL
```

This rule must remain obvious in:

- backend architecture
- database
- API
- UI
- documentation
- tests

---

# 126. STATIC ANALYTICS BUSINESS RULE

Our platform cannot know that a purely Static QR was scanned because the request does not reach our server.

Never display fake:

```text
Scan count
Device count
Country count
```

for Static QR.

If the Static QR contains a website URL with UTM parameters, explain:

```text
Traffic may be measured by analytics installed on the destination website.
```

That is external analytics, not QR platform scan analytics.

---

# 127. DYNAMIC QR IMMUTABILITY RULE

Once a Dynamic QR has been physically distributed, the most important stable identifier is:

```text
short URL / slug
```

Therefore:

- destination is editable
- display name is editable
- campaign is editable
- rules are editable
- slug should be immutable by default

This protects already printed QR codes.

---

# 128. DESIGN THE APPLICATION AS A REAL PRODUCT

Do not produce:

- a demo landing page
- one giant controller
- one giant React component
- fake charts
- fake scan numbers
- placeholder buttons
- TODOs everywhere
- hard-coded demo destinations
- hard-coded production URLs

Build actual working functionality.

---

# 129. IMPLEMENTATION PHASES

Implement in this order.

## PHASE 1 — FOUNDATION

- Laravel project
- React frontend
- PostgreSQL
- Redis
- authentication
- workspace foundation
- dashboard shell
- migrations
- domain enums
- clean project structure

## PHASE 2 — STATIC QR

- static QR model
- URL
- text
- email
- phone
- SMS
- Wi-Fi
- vCard
- location
- payload builders
- QR generation
- PNG
- SVG
- design options
- download
- tests

## PHASE 3 — DYNAMIC QR CORE

- dynamic QR creation
- slug generation
- custom aliases
- destination storage
- QR generation
- short domain route
- redirect resolver
- HTTP 302
- Redis redirect caching
- edit destination
- destination history
- pause/archive
- expiration
- tests

## PHASE 4 — ANALYTICS

- scan event
- queue
- Redis worker
- QR scan table
- human/bot distinction
- estimated unique scans
- device parsing
- country data
- analytics dashboard
- charts
- tests

## PHASE 5 — ORGANIZATION

- campaigns
- campaign analytics
- folders
- tags
- filtering
- search

## PHASE 6 — ADVANCED DYNAMIC FEATURES

- UTM
- scan limit
- fallback
- password/PIN
- device redirect
- country redirect
- language redirect
- date redirect
- A/B rules

## PHASE 7 — PRODUCT HARDENING

- audit log
- CSV export
- API tokens
- custom domain foundation
- performance optimization
- security review
- accessibility review
- production documentation

---

# 130. MVP DEFINITION

The application is already useful when the following work correctly:

### Static

```text
Create Static QR
Choose type
Enter content
Customize design
Preview
Download PNG
Download SVG
```

### Dynamic

```text
Create Dynamic QR
Enter destination
Generate slug
Generate QR
Scan QR
Redirect
Count scan
Edit destination
Same QR now redirects to new destination
View analytics
```

This core must work flawlessly before spending excessive time on advanced features.

---

# 131. ACCEPTANCE TEST — STATIC

Scenario:

1. Create Static URL QR
2. Destination:

```text
https://example.com/test
```

3. Download QR
4. Decode QR

Expected payload:

```text
https://example.com/test
```

There must NOT be an application redirect URL inside it.

---

# 132. ACCEPTANCE TEST — DYNAMIC

Scenario:

Create Dynamic QR:

```text
destination:
https://example.com/page-a
```

Generated QR contains:

```text
https://q.example.com/abc123
```

Scan/open:

```text
https://q.example.com/abc123
```

Expected:

```text
302 → https://example.com/page-a
```

Change destination to:

```text
https://example.com/page-b
```

Do NOT regenerate QR.

Open the same:

```text
https://q.example.com/abc123
```

Expected:

```text
302 → https://example.com/page-b
```

This acceptance test is critical.

---

# 133. ACCEPTANCE TEST — ANALYTICS

Opening a Dynamic QR:

```text
/q/abc123
```

should:

1. redirect correctly
2. enqueue analytics
3. eventually create scan analytics
4. update total scans
5. not store raw IP
6. classify obvious bots separately

Redirect must work even if analytics processing temporarily fails.

Analytics failure must NOT break the QR redirect.

---

# 134. ACCEPTANCE TEST — STATIC WIFI

Create Wi-Fi QR:

```text
SSID: Office
Security: WPA
Password: Test123456
```

The resulting QR must be recognized by common mobile QR scanners as a Wi-Fi configuration.

---

# 135. ACCEPTANCE TEST — PRINT QUALITY

SVG QR:

- must be true vector output
- must preserve quiet zone
- must preserve QR geometry
- must remain valid when scaled

PNG export:

- must remain crisp
- must not apply image smoothing that damages modules

---

# 136. CODE QUALITY

Use:

```text
PSR standards
Laravel conventions
strict typing where appropriate
Form Requests
API Resources
Policies
Services
Enums
DTOs when useful
database constraints
transactions
tests
```

Avoid:

```text
fat controllers
massive components
duplicate business logic
magic strings
untyped frontend state
```

---

# 137. DOCUMENT IMPORTANT DECISIONS

Create:

```text
docs/ARCHITECTURE.md
```

Explain:

- why Static/Dynamic are separate concepts
- redirect flow
- analytics flow
- caching
- privacy
- rule engine
- short domain architecture
- future custom-domain support

Create:

```text
docs/DATABASE.md
```

Include table relationships.

Create:

```text
docs/DEPLOYMENT.md
```

Include production requirements.

---

# 138. UI LABELS

Use clear language.

Preferred:

```text
Static QR
Direct QR

Dynamic QR
Smart QR
```

The primary official technical names remain:

```text
Static
Dynamic
```

Descriptions should help non-technical users understand them.

---

# 139. EMPTY STATES

Create professional empty states.

Example:

```text
You don't have any QR codes yet.

Create a Static QR for permanent content or a Dynamic QR if you need tracking and editable destinations.

[ Create QR Code ]
```

---

# 140. CONFIRMATION DIALOGS

Require confirmation for dangerous actions:

```text
Archive QR
Delete QR
Disable redirect
Change behavior that may affect printed QR
```

Do NOT require annoying confirmation for every normal edit.

---

# 141. MOBILE DASHBOARD

The dashboard must remain usable on mobile.

QR preview/download in particular should work well on phones.

Data tables may become cards or horizontal-scroll intelligently.

---

# 142. DARK MODE

Support:

```text
Light
Dark
System
```

Persist preference.

Do not design only one mode and invert colors carelessly.

---

# 143. FINAL DELIVERY REQUIREMENTS

When implementation is complete, provide:

1. concise architecture summary
2. list of completed features
3. list of incomplete/future features
4. important environment variables
5. database migration command
6. frontend build command
7. queue worker command
8. scheduler requirement
9. test command
10. production deployment checklist

---

# 144. DO NOT STOP AT ANALYSIS

Begin by inspecting the repository.

Then create/update:

```text
docs/IMPLEMENTATION.md
```

After that immediately start implementing Phase 1.

Continue through the phases systematically.

When making architectural decisions, use sensible production-grade defaults instead of repeatedly asking for confirmation.

Only stop and ask for input when an external credential, unavailable infrastructure dependency, or genuinely ambiguous business requirement makes further implementation impossible.

Otherwise continue implementing.

---

# 145. FINAL PRODUCT EXPECTATION

The finished system should allow the following real-world workflow:

```text
Administrator logs in
        ↓
Create QR
        ↓
Choose Static or Dynamic
        ↓
Configure content
        ↓
Customize QR design
        ↓
Preview
        ↓
Generate
        ↓
Download SVG/PNG
```

For Static:

```text
Phone scans QR
        ↓
final content opens directly
```

For Dynamic:

```text
Phone scans QR
        ↓
short domain
        ↓
fast redirect resolver
        ↓
analytics queued
        ↓
optional redirect rules
        ↓
302 redirect
        ↓
destination
```

Dashboard later shows:

```text
Total Scans
Estimated Unique Scans
Human Scans
Bot Requests
Devices
Countries
Campaign Results
Top QR Codes
```

The owner must retain full control of:

```text
domain
redirects
database
analytics
QR images
destinations
```

The application must not depend on a third-party QR management subscription to keep already-created Dynamic QR codes working.

Build this as maintainable production software, not a prototype.