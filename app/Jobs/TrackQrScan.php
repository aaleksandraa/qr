<?php

namespace App\Jobs;

use App\Models\QrCode;
use App\Models\QrScan;
use App\Models\QrScanDailyStat;
use App\Services\Analytics\BotDetector;
use App\Services\Analytics\DeviceDetector;
use App\Services\Analytics\VisitorHasher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class TrackQrScan implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly int $qrCodeId,
        public readonly string $scannedAt,
        public readonly ?string $ip,
        public readonly ?string $userAgent,
        public readonly ?string $referrer,
        public readonly ?string $acceptLanguage,
        public readonly ?string $countryHint,
        public readonly ?string $regionHint,
        public readonly ?string $cityHint,
        public readonly string $requestId,
        public readonly ?string $variant = null,
    ) {}

    public function handle(
        BotDetector $botDetector,
        DeviceDetector $deviceDetector,
        VisitorHasher $visitorHasher,
    ): void {
        $qr = QrCode::query()->find($this->qrCodeId);
        if (! $qr) {
            return;
        }

        $device = $deviceDetector->detect($this->userAgent);
        $isBot = $botDetector->isBot($this->userAgent);
        $hash = $visitorHasher->hash($this->ip, $this->userAgent);
        $scannedAt = now()->parse($this->scannedAt);
        $storeIp = (bool) config('qr.analytics.store_raw_ip', false);

        $alreadyUnique = QrScan::query()
            ->where('qr_code_id', $qr->id)
            ->where('visitor_hash', $hash)
            ->where('is_bot', false)
            ->exists();

        QrScan::create([
            'qr_code_id' => $qr->id,
            'scanned_at' => $scannedAt,
            'visitor_hash' => $hash,
            'country_code' => $this->normalizeCountry($this->countryHint),
            'country_name' => null,
            'region' => $this->cityHint ? null : $this->regionHint,
            'city' => $this->cityHint,
            'device_type' => $device['device_type'],
            'os' => $device['os'],
            'browser' => $device['browser'],
            'referrer' => $this->referrer ? mb_substr($this->referrer, 0, 512) : null,
            'user_agent_summary' => $device['summary'],
            'is_bot' => $isBot,
            'ab_variant' => $this->variant,
            'request_id' => $this->requestId,
            'ip_address' => $storeIp ? $this->ip : null,
        ]);

        $uniqueIncrement = (! $isBot && ! $alreadyUnique) ? 1 : 0;

        QrCode::query()->whereKey($qr->id)->update([
            'total_scans' => DB::raw('total_scans + 1'),
            'human_scans' => DB::raw('human_scans + '.($isBot ? 0 : 1)),
            'bot_scans' => DB::raw('bot_scans + '.($isBot ? 1 : 0)),
            'estimated_unique_scans' => DB::raw('estimated_unique_scans + '.$uniqueIncrement),
            'last_scanned_at' => $scannedAt,
        ]);

        $stat = QrScanDailyStat::query()->firstOrCreate(
            ['qr_code_id' => $qr->id, 'date' => $scannedAt->toDateString()],
            ['total_scans' => 0, 'human_scans' => 0, 'bot_scans' => 0, 'unique_scans' => 0],
        );

        $stat->increment('total_scans');
        $stat->increment($isBot ? 'bot_scans' : 'human_scans');
        if ($uniqueIncrement === 1) {
            $stat->increment('unique_scans');
        }
    }

    public function failed(?Throwable $exception): void
    {
        Log::warning('qr.analytics.job_failed', [
            'qr_code_id' => $this->qrCodeId,
            'message' => $exception?->getMessage(),
        ]);
    }

    private function normalizeCountry(?string $code): ?string
    {
        if (! $code || strtoupper($code) === 'XX') {
            return null;
        }

        $code = strtoupper($code);

        return strlen($code) === 2 ? $code : null;
    }
}
