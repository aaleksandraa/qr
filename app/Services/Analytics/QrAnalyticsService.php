<?php

namespace App\Services\Analytics;

use App\Enums\QrCodeType;
use App\Models\Campaign;
use App\Models\QrCode;
use App\Models\QrScan;
use App\Models\Workspace;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class QrAnalyticsService
{
    /**
     * @return array<string, mixed>
     */
    public function dashboard(Workspace $workspace, CarbonImmutable $from, CarbonImmutable $to): array
    {
        $qrQuery = QrCode::query()->inWorkspace($workspace);

        $scansToday = QrScan::query()
            ->whereIn('qr_code_id', $qrQuery->clone()->dynamic()->select('id'))
            ->whereDate('scanned_at', now()->toDateString())
            ->where('is_bot', false)
            ->count();

        $scansMonth = QrScan::query()
            ->whereIn('qr_code_id', $qrQuery->clone()->dynamic()->select('id'))
            ->where('scanned_at', '>=', now()->startOfMonth())
            ->where('is_bot', false)
            ->count();

        return [
            'total_qr_codes' => $qrQuery->clone()->count(),
            'static_qr_codes' => $qrQuery->clone()->static()->count(),
            'dynamic_qr_codes' => $qrQuery->clone()->dynamic()->count(),
            'scans_today' => $scansToday,
            'scans_this_month' => $scansMonth,
            'active_campaigns' => $workspace->campaigns()->where('status', 'active')->count(),
            'timeline' => $this->timeline($workspace, $from, $to),
            'top_qr_codes' => $this->topQrCodes($workspace, 8),
            'top_campaigns' => $this->topCampaigns($workspace, 5),
            'devices' => $this->breakdown($workspace, 'device_type', $from, $to),
            'countries' => $this->breakdown($workspace, 'country_code', $from, $to),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function forQr(QrCode $qr, CarbonImmutable $from, CarbonImmutable $to): array
    {
        if ($qr->qr_type !== QrCodeType::Dynamic) {
            return [
                'supported' => false,
                'message' => 'Static QR codes have no platform scan analytics because scans never reach this server.',
            ];
        }

        $base = QrScan::query()
            ->where('qr_code_id', $qr->id)
            ->whereBetween('scanned_at', [$from, $to]);

        return [
            'supported' => true,
            'unique_is_estimate' => true,
            'location_note' => 'Approximate location based on IP',
            'total_scans' => $qr->total_scans,
            'human_scans' => $qr->human_scans,
            'bot_scans' => $qr->bot_scans,
            'estimated_unique_scans' => $qr->estimated_unique_scans,
            'scans_today' => (clone $base)->whereDate('scanned_at', now()->toDateString())->where('is_bot', false)->count(),
            'last_scanned_at' => $qr->last_scanned_at?->toIso8601String(),
            'range_total' => (clone $base)->count(),
            'range_human' => (clone $base)->where('is_bot', false)->count(),
            'range_bots' => (clone $base)->where('is_bot', true)->count(),
            'timeline' => $this->qrTimeline($qr, $from, $to),
            'devices' => $this->qrBreakdown($qr, 'device_type', $from, $to),
            'os' => $this->qrBreakdown($qr, 'os', $from, $to),
            'browsers' => $this->qrBreakdown($qr, 'browser', $from, $to),
            'countries' => $this->qrBreakdown($qr, 'country_code', $from, $to),
            'latest' => $qr->scans()
                ->latest('scanned_at')
                ->limit(25)
                ->get([
                    'id', 'scanned_at', 'country_code', 'city', 'device_type', 'os', 'browser', 'is_bot', 'referrer',
                ]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function forCampaign(Campaign $campaign, CarbonImmutable $from, CarbonImmutable $to): array
    {
        $qrIds = $campaign->qrCodes()->dynamic()->pluck('id');

        $scans = QrScan::query()
            ->whereIn('qr_code_id', $qrIds)
            ->whereBetween('scanned_at', [$from, $to]);

        $ranking = $campaign->qrCodes()
            ->dynamic()
            ->orderByDesc('human_scans')
            ->get(['public_id', 'name', 'slug', 'total_scans', 'human_scans', 'estimated_unique_scans']);

        return [
            'qr_count' => $campaign->qrCodes()->count(),
            'total_scans' => (clone $scans)->count(),
            'human_scans' => (clone $scans)->where('is_bot', false)->count(),
            'estimated_unique' => $campaign->qrCodes()->sum('estimated_unique_scans'),
            'ranking' => $ranking,
        ];
    }

    /**
     * @return list<array{date: string, scans: int}>
     */
    private function timeline(Workspace $workspace, CarbonImmutable $from, CarbonImmutable $to): array
    {
        return QrScan::query()
            ->selectRaw('DATE(scanned_at) as date, COUNT(*) as scans')
            ->whereIn('qr_code_id', QrCode::query()->inWorkspace($workspace)->dynamic()->select('id'))
            ->where('is_bot', false)
            ->whereBetween('scanned_at', [$from, $to])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => ['date' => $row->date, 'scans' => (int) $row->scans])
            ->all();
    }

    /**
     * @return Collection<int, QrCode>
     */
    private function topQrCodes(Workspace $workspace, int $limit)
    {
        return QrCode::query()
            ->inWorkspace($workspace)
            ->dynamic()
            ->orderByDesc('human_scans')
            ->limit($limit)
            ->get(['public_id', 'name', 'slug', 'human_scans', 'total_scans', 'estimated_unique_scans']);
    }

    /**
     * @return Collection<int, Campaign>
     */
    private function topCampaigns(Workspace $workspace, int $limit)
    {
        return Campaign::query()
            ->where('workspace_id', $workspace->id)
            ->withSum('qrCodes as human_scans', 'human_scans')
            ->orderByDesc('human_scans')
            ->limit($limit)
            ->get(['id', 'public_id', 'name']);
    }

    /**
     * @return list<array{label: string, value: int}>
     */
    private function breakdown(Workspace $workspace, string $column, CarbonImmutable $from, CarbonImmutable $to): array
    {
        return QrScan::query()
            ->select($column.' as label', DB::raw('COUNT(*) as value'))
            ->whereIn('qr_code_id', QrCode::query()->inWorkspace($workspace)->dynamic()->select('id'))
            ->where('is_bot', false)
            ->whereBetween('scanned_at', [$from, $to])
            ->whereNotNull($column)
            ->groupBy($column)
            ->orderByDesc('value')
            ->limit(8)
            ->get()
            ->map(fn ($row) => ['label' => (string) $row->label, 'value' => (int) $row->value])
            ->all();
    }

    /**
     * @return list<array{date: string, scans: int}>
     */
    private function qrTimeline(QrCode $qr, CarbonImmutable $from, CarbonImmutable $to): array
    {
        return QrScan::query()
            ->selectRaw('DATE(scanned_at) as date, COUNT(*) as scans')
            ->where('qr_code_id', $qr->id)
            ->where('is_bot', false)
            ->whereBetween('scanned_at', [$from, $to])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => ['date' => $row->date, 'scans' => (int) $row->scans])
            ->all();
    }

    /**
     * @return list<array{label: string, value: int}>
     */
    private function qrBreakdown(QrCode $qr, string $column, CarbonImmutable $from, CarbonImmutable $to): array
    {
        return QrScan::query()
            ->select($column.' as label', DB::raw('COUNT(*) as value'))
            ->where('qr_code_id', $qr->id)
            ->where('is_bot', false)
            ->whereBetween('scanned_at', [$from, $to])
            ->whereNotNull($column)
            ->groupBy($column)
            ->orderByDesc('value')
            ->limit(10)
            ->get()
            ->map(fn ($row) => ['label' => (string) $row->label, 'value' => (int) $row->value])
            ->all();
    }
}
