<?php

namespace App\Http\Controllers;

use App\Services\Analytics\QrAnalyticsService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsController extends Controller
{
    public function __invoke(Request $request, QrAnalyticsService $analytics): Response
    {
        $workspace = $request->user()->currentWorkspace();
        abort_unless($workspace, 403);

        $range = $request->string('range', '30')->toString();
        $days = match ($range) {
            '1' => 0,
            '7' => 6,
            '90' => 89,
            default => 29,
        };

        $from = CarbonImmutable::now('UTC')->subDays($days)->startOfDay();
        $to = CarbonImmutable::now('UTC')->endOfDay();

        return Inertia::render('analytics/index', [
            'range' => $range,
            'stats' => $analytics->dashboard($workspace, $from, $to),
        ]);
    }
}
