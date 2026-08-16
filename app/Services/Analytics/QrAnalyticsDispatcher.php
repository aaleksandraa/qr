<?php

namespace App\Services\Analytics;

use App\Jobs\TrackQrScan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class QrAnalyticsDispatcher
{
    public function dispatch(int $qrCodeId, Request $request, ?string $variant = null): void
    {
        if (! config('qr.analytics.enabled')) {
            return;
        }

        try {
            TrackQrScan::dispatch(
                qrCodeId: $qrCodeId,
                scannedAt: now()->toIso8601String(),
                ip: $request->ip(),
                userAgent: (string) $request->userAgent(),
                referrer: $request->headers->get('referer'),
                acceptLanguage: $request->headers->get('accept-language'),
                countryHint: $request->headers->get('CF-IPCountry') ?? $request->headers->get('X-Country-Code'),
                regionHint: $request->headers->get('X-Region') ?? $request->headers->get('CF-Region'),
                cityHint: $request->headers->get('X-City') ?? $request->headers->get('CF-IPCity'),
                requestId: (string) Str::ulid(),
                variant: $variant,
            )->onQueue((string) config('qr.analytics.queue', 'analytics'));
        } catch (Throwable $e) {
            Log::warning('qr.analytics.dispatch_failed', [
                'qr_code_id' => $qrCodeId,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
