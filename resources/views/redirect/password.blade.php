<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Protected QR</title>
    <style>
        :root { color-scheme: light dark; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; font-family: ui-sans-serif, system-ui, sans-serif; background: #f8fafc; color: #0f172a; padding: 24px; }
        .card { max-width: 24rem; width: 100%; background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 32px; }
        h1 { font-size: 1.25rem; margin: 0 0 8px; }
        p, .error { margin: 0 0 16px; color: #475569; line-height: 1.5; }
        .error { color: #b91c1c; }
        input, button { width: 100%; box-sizing: border-box; font: inherit; border-radius: 10px; padding: 12px; }
        input { border: 1px solid #cbd5e1; margin-bottom: 12px; }
        button { border: 0; background: #111827; color: #fff; cursor: pointer; }
        @media (prefers-color-scheme: dark) {
            body { background: #0b1220; color: #e2e8f0; }
            .card { background: #111827; border-color: #1f2937; }
            p { color: #94a3b8; }
            input { background: #0b1220; color: #e2e8f0; border-color: #334155; }
            button { background: #e2e8f0; color: #0f172a; }
        }
    </style>
</head>
<body>
    <main class="card">
        <h1>This QR is protected</h1>
        <p>Enter the PIN or password to continue.</p>
        @if (!empty($error))
            <p class="error">{{ $error }}</p>
        @endif
        <form method="post" action="{{ url()->current() }}">
            @csrf
            <label class="sr-only" for="password">Password</label>
            <input id="password" name="password" type="password" required autocomplete="current-password" autofocus>
            <button type="submit">Continue</button>
        </form>
    </main>
</body>
</html>
