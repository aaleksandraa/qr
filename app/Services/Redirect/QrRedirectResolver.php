<?php

namespace App\Services\Redirect;

use App\Enums\QrStatus;
use App\Exceptions\Redirect\QrRedirectException;
use App\Models\QrCode;
use App\Services\Analytics\DeviceDetector;
use App\Services\Analytics\QrAnalyticsDispatcher;
use App\Services\Analytics\VisitorHasher;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class QrRedirectResolver
{
    public function __construct(
        private readonly QrRedirectCache $cache,
        private readonly QrRuleEngine $ruleEngine,
        private readonly QrAnalyticsDispatcher $analyticsDispatcher,
        private readonly DeviceDetector $deviceDetector,
        private readonly VisitorHasher $visitorHasher,
    ) {}

    /**
     * @return array{url: string, status: int}
     */
    public function resolve(string $slug, Request $request): array
    {
        $cached = $this->cache->get($slug);

        if ($cached === null) {
            $qr = QrCode::query()->with('redirectRules')->where('slug', $slug)->first();
            if (! $qr) {
                throw new QrRedirectException('not_found');
            }
            $cached = $this->cache->serialize($qr);
            $this->cache->put($qr);
        }

        if ($fallback = $this->forcedFallback($cached)) {
            if ($cached['tracking_enabled'] ?? true) {
                $this->analyticsDispatcher->dispatch((int) $cached['id'], $request);
            }

            return [
                'url' => $fallback,
                'status' => (int) config('qr.redirect_status', 302),
            ];
        }

        $this->assertAvailable($cached);

        $context = $this->context($request);
        $destination = $this->ruleEngine->resolve($cached, $context);
        $destination = $this->applyUtm($destination, $cached['utm_parameters'] ?? []);

        if ($cached['tracking_enabled'] ?? true) {
            $this->incrementScanCounter((int) $cached['id'], $cached['max_scans'] ?? null);
            $this->analyticsDispatcher->dispatch((int) $cached['id'], $request);
        }

        return [
            'url' => $destination,
            'status' => (int) config('qr.redirect_status', 302),
        ];
    }

    /**
     * @param  array<string, mixed>  $cached
     */
    private function forcedFallback(array $cached): ?string
    {
        if (
            ! empty($cached['expires_at'])
            && CarbonImmutable::now('UTC')->gte(CarbonImmutable::parse($cached['expires_at'])->utc())
            && ($cached['expired_behavior'] ?? 'page') === 'fallback'
            && filled($cached['fallback_url'] ?? null)
        ) {
            return (string) $cached['fallback_url'];
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $cached
     */
    private function assertAvailable(array $cached): void
    {
        $status = $cached['status'] ?? null;

        if ($status === QrStatus::Paused->value) {
            throw new QrRedirectException('paused', $cached['fallback_url'] ?? null);
        }

        if ($status === QrStatus::Archived->value) {
            throw new QrRedirectException('unavailable', $cached['fallback_url'] ?? null);
        }

        $now = CarbonImmutable::now('UTC');

        if (! empty($cached['starts_at']) && $now->lt(CarbonImmutable::parse($cached['starts_at'])->utc())) {
            throw new QrRedirectException('not_started', $cached['fallback_url'] ?? null);
        }

        if (! empty($cached['expires_at']) && $now->gte(CarbonImmutable::parse($cached['expires_at'])->utc())) {
            throw new QrRedirectException('expired', $cached['fallback_url'] ?? null);
        }

        if (! empty($cached['password_protected'])) {
            $unlocked = session('qr_unlocked_'.$cached['id']);
            if (! $unlocked) {
                throw new QrRedirectException('password', null);
            }
        }
    }

    private function incrementScanCounter(int $qrId, ?int $maxScans): void
    {
        if (! $maxScans) {
            return;
        }

        try {
            $key = config('qr.cache.scan_counter_prefix', 'qr:scans:').$qrId;
            $count = Cache::increment($key);
            if ($count === 1) {
                Cache::put($key, $count, now()->addYear());
            }
            if ($count > $maxScans) {
                throw new QrRedirectException('limit');
            }
        } catch (QrRedirectException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::warning('qr.redirect.counter_failed', ['id' => $qrId, 'message' => $e->getMessage()]);
        }
    }

    private function context(Request $request): RedirectContext
    {
        $ua = (string) $request->userAgent();

        return new RedirectContext(
            request: $request,
            userAgent: $ua,
            acceptLanguage: $request->headers->get('accept-language'),
            countryCode: $request->headers->get('CF-IPCountry') ?? $request->headers->get('X-Country-Code'),
            visitorHash: $this->visitorHasher->hash($request->ip(), $ua),
            deviceFamily: $this->deviceDetector->deviceFamily($ua),
            osFamily: $this->deviceDetector->osFamily($ua),
        );
    }

    /**
     * @param  array<string, string>  $utm
     */
    private function applyUtm(string $url, array $utm): string
    {
        $utm = array_filter($utm, fn ($value) => filled($value));
        if ($utm === []) {
            return $url;
        }

        $parts = parse_url($url);
        if ($parts === false || empty($parts['host'])) {
            return $url;
        }

        $existing = [];
        if (! empty($parts['query'])) {
            parse_str($parts['query'], $existing);
        }

        $query = http_build_query(array_merge($existing, $utm));
        $rebuilt = ($parts['scheme'] ?? 'https').'://'.$parts['host'];
        if (! empty($parts['port'])) {
            $rebuilt .= ':'.$parts['port'];
        }
        $rebuilt .= $parts['path'] ?? '';
        $rebuilt .= '?'.$query;
        if (! empty($parts['fragment'])) {
            $rebuilt .= '#'.$parts['fragment'];
        }

        return $rebuilt;
    }
}
