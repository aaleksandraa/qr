<?php

namespace App\Http\Controllers;

use App\Enums\CampaignStatus;
use App\Models\Campaign;
use App\Services\Analytics\QrAnalyticsService;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CampaignController extends Controller
{
    public function index(Request $request): Response
    {
        $workspace = $request->user()->currentWorkspace();
        abort_unless($workspace, 403);

        $campaigns = Campaign::query()
            ->where('workspace_id', $workspace->id)
            ->withCount('qrCodes')
            ->withSum('qrCodes as human_scans', 'human_scans')
            ->latest()
            ->paginate(20);

        return Inertia::render('campaigns/index', [
            'campaigns' => $campaigns,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $workspace = $request->user()->currentWorkspace();
        abort_unless($workspace, 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:2000'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        Campaign::create([
            ...$data,
            'workspace_id' => $workspace->id,
            'created_by' => $request->user()->id,
            'status' => CampaignStatus::Active,
        ]);

        return back()->with('success', 'Campaign created.');
    }

    public function show(Request $request, Campaign $campaign, QrAnalyticsService $analytics): Response
    {
        $this->authorize('view', $campaign);

        $from = CarbonImmutable::now('UTC')->subDays(29)->startOfDay();
        $to = CarbonImmutable::now('UTC')->endOfDay();

        return Inertia::render('campaigns/show', [
            'campaign' => $campaign,
            'analytics' => $analytics->forCampaign($campaign, $from, $to),
        ]);
    }

    public function update(Request $request, Campaign $campaign): RedirectResponse
    {
        $this->authorize('update', $campaign);

        $campaign->update($request->validate([
            'name' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', 'in:draft,active,paused,ended'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
        ]));

        return back()->with('success', 'Campaign updated.');
    }

    public function destroy(Campaign $campaign): RedirectResponse
    {
        $this->authorize('delete', $campaign);
        $campaign->delete();

        return redirect()->route('campaigns.index')->with('success', 'Campaign archived.');
    }
}
