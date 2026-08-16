<?php

namespace App\Http\Controllers;

use App\Services\Analytics\QrAnalyticsService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request, QrAnalyticsService $analytics): Response
    {
        $workspace = $request->user()->currentWorkspace();
        $from = CarbonImmutable::now('UTC')->subDays(29)->startOfDay();
        $to = CarbonImmutable::now('UTC')->endOfDay();

        return Inertia::render('dashboard', [
            'stats' => $workspace ? $analytics->dashboard($workspace, $from, $to) : $this->emptyStats(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyStats(): array
    {
        return [
            'total_qr_codes' => 0,
            'static_qr_codes' => 0,
            'dynamic_qr_codes' => 0,
            'scans_today' => 0,
            'scans_this_month' => 0,
            'active_campaigns' => 0,
            'timeline' => [],
            'top_qr_codes' => [],
            'top_campaigns' => [],
            'devices' => [],
            'countries' => [],
        ];
    }
}
