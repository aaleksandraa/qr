<?php

namespace App\Services\Analytics;

class VisitorHasher
{
    public function hash(?string $ip, ?string $userAgent): string
    {
        $secret = (string) config('qr.analytics.hash_secret');
        $normalizedUa = strtolower(trim((string) $userAgent));

        return hash_hmac('sha256', ($ip ?? 'unknown').'|'.$normalizedUa, $secret);
    }
}
