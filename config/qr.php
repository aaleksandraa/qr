<?php

return [
    'short_base_url' => env('QR_SHORT_BASE_URL', env('APP_URL', 'http://localhost:8000').'/r'),

    'short_host' => env('QR_SHORT_HOST'),

    'redirect_status' => (int) env('QR_REDIRECT_STATUS', 302),

    'default_error_correction' => env('QR_DEFAULT_ERROR_CORRECTION', 'M'),

    'default_format' => env('QR_DEFAULT_FORMAT', 'svg'),

    'slug' => [
        'length' => (int) env('QR_SLUG_LENGTH', 7),
        'min_custom_length' => (int) env('QR_SLUG_MIN_LENGTH', 3),
        'max_custom_length' => (int) env('QR_SLUG_MAX_LENGTH', 64),
        'reserved' => [
            'admin', 'api', 'login', 'register', 'dashboard', 'analytics',
            'settings', 'qr', 'qrs', 'health', 'up', 'r', 'robots.txt',
            'favicon.ico', 'horizon', 'sanctum', 'password', 'reset',
            'campaigns', 'folders', 'tags', 'account', 'users', 'workspace',
            'workspaces', 'docs', 'static', 'dynamic', 'create', 'export',
        ],
    ],

    'allowed_destination_schemes' => ['https', 'http'],

    'forbidden_destination_schemes' => ['javascript', 'data', 'file', 'ftp', 'blob', 'about'],

    'domain_allowlist' => array_values(array_filter(array_map('trim', explode(',', (string) env('QR_DOMAIN_ALLOWLIST', ''))))),

    'domain_blocklist' => array_values(array_filter(array_map('trim', explode(',', (string) env('QR_DOMAIN_BLOCKLIST', ''))))),

    'analytics' => [
        'enabled' => (bool) env('QR_ANALYTICS_ENABLED', true),
        'retention_days' => (int) env('QR_ANALYTICS_RETENTION_DAYS', 365),
        'hash_secret' => env('QR_ANALYTICS_HASH_SECRET', env('APP_KEY')),
        'store_raw_ip' => (bool) env('QR_ANALYTICS_STORE_RAW_IP', false),
        'queue' => env('QR_ANALYTICS_QUEUE', 'analytics'),
    ],

    'cache' => [
        'redirect_prefix' => 'qr:redirect:',
        'scan_counter_prefix' => 'qr:scans:',
        'ttl' => (int) env('QR_REDIRECT_CACHE_TTL', 3600),
    ],

    'design' => [
        'min_contrast_ratio' => 1.8,
        'warn_contrast_ratio' => 3.0,
        'min_quiet_zone' => 4,
        'max_logo_ratio' => 0.22,
        'max_text_payload' => 1200,
        'dense_payload_warning' => 400,
    ],

    'export' => [
        'png_sizes' => [512, 1024, 2048],
        'default_png_size' => 1024,
        'default_svg_size' => 512,
    ],

    'public_registration' => (bool) env('PUBLIC_REGISTRATION', false),

    'registration' => [
        'min_seconds' => (int) env('QR_REGISTRATION_MIN_SECONDS', 2),
        'max_per_minute' => (int) env('QR_REGISTRATION_MAX_PER_MINUTE', 5),
        'max_per_hour' => (int) env('QR_REGISTRATION_MAX_PER_HOUR', 8),
        'max_per_day' => (int) env('QR_REGISTRATION_MAX_PER_DAY', 12),
        'honeypot_field' => 'website',
        'disposable_domains' => [
            'mailinator.com', 'guerrillamail.com', '10minutemail.com', 'tempmail.com',
            'temp-mail.org', 'yopmail.com', 'trashmail.com', 'discard.email',
            'getnada.com', 'sharklasers.com', 'guerrillamailblock.com', 'spam4.me',
            'mailnesia.com', 'maildrop.cc', 'fakeinbox.com', 'throwaway.email',
        ],
        'turnstile' => [
            'site_key' => env('TURNSTILE_SITE_KEY'),
            'secret_key' => env('TURNSTILE_SECRET_KEY'),
            'verify_url' => 'https://challenges.cloudflare.com/turnstile/v0/siteverify',
        ],
    ],

    'geo_resolver' => env('QR_GEO_RESOLVER', 'header'),
];
