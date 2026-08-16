<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>QR unavailable</title>
    <style>
        :root { color-scheme: light dark; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; font-family: ui-sans-serif, system-ui, sans-serif; background: #f8fafc; color: #0f172a; padding: 24px; }
        .card { max-width: 28rem; width: 100%; background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 32px; }
        h1 { font-size: 1.25rem; margin: 0 0 8px; }
        p { margin: 0; color: #475569; line-height: 1.5; }
        @media (prefers-color-scheme: dark) {
            body { background: #0b1220; color: #e2e8f0; }
            .card { background: #111827; border-color: #1f2937; }
            p { color: #94a3b8; }
        }
    </style>
</head>
<body>
    <main class="card">
        @php
            $copy = match ($reason ?? 'unavailable') {
                'paused' => ['This QR code is currently unavailable.', 'The owner has temporarily paused this link.'],
                'expired' => ['This QR code has expired.', 'The destination is no longer active.'],
                'not_started' => ['This QR code is not active yet.', 'Please try again later.'],
                'limit' => ['This QR code has reached its scan limit.', 'The destination is no longer available.'],
                'not_found' => ['QR code not found.', 'This link is invalid or has been removed.'],
                default => ['This QR code is currently unavailable.', 'Please contact the owner if you expected this link to work.'],
            };
        @endphp
        <h1>{{ $copy[0] }}</h1>
        <p>{{ $copy[1] }}</p>
    </main>
</body>
</html>
