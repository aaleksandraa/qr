<?php

namespace App\Services\Security;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class RegistrationGuard
{
    public function assertSafe(Request $request): void
    {
        $this->assertHoneypot($request);
        $this->assertHumanTiming($request);
        $this->assertNotDisposable((string) $request->input('email'));
        $this->assertTurnstile($request);
    }

    private function assertHoneypot(Request $request): void
    {
        $field = (string) config('qr.registration.honeypot_field', 'website');

        if (filled($request->input($field))) {
            Log::info('qr.registration.honeypot');
            throw ValidationException::withMessages([
                'email' => __('Unable to create this account. Please try again.'),
            ]);
        }
    }

    private function assertHumanTiming(Request $request): void
    {
        $minimum = (int) config('qr.registration.min_seconds', 2);

        if ($minimum <= 0) {
            return;
        }

        $started = (int) $request->input('form_started_at');
        $elapsed = $started > 0 ? time() - $started : 0;

        if ($elapsed < $minimum || $elapsed > 7200) {
            Log::info('qr.registration.too_fast_or_stale');
            throw ValidationException::withMessages([
                'email' => __('Please wait a moment and try again.'),
            ]);
        }
    }

    private function assertNotDisposable(string $email): void
    {
        $domain = strtolower((string) substr(strrchr($email, '@') ?: '', 1));
        $blocked = array_map('strtolower', config('qr.registration.disposable_domains', []));

        if ($domain !== '' && in_array($domain, $blocked, true)) {
            throw ValidationException::withMessages([
                'email' => __('Use a permanent work or personal email address.'),
            ]);
        }
    }

    private function assertTurnstile(Request $request): void
    {
        $secret = (string) config('qr.registration.turnstile.secret_key');

        if ($secret === '') {
            return;
        }

        $token = (string) $request->input('cf-turnstile-response');

        if ($token === '') {
            throw ValidationException::withMessages([
                'email' => __('Please complete the security check.'),
            ]);
        }

        $verified = Http::asForm()
            ->timeout(5)
            ->post((string) config('qr.registration.turnstile.verify_url'), [
                'secret' => $secret,
                'response' => $token,
                'remoteip' => $request->ip(),
            ])
            ->json('success');

        if ($verified !== true) {
            Log::info('qr.registration.turnstile_failed');
            throw ValidationException::withMessages([
                'email' => __('Security check failed. Please try again.'),
            ]);
        }
    }
}
