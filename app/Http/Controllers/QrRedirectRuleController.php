<?php

namespace App\Http\Controllers;

use App\Enums\RedirectRuleType;
use App\Models\QrCode;
use App\Models\QrRedirectRule;
use App\Services\Redirect\QrRedirectCache;
use App\Support\DestinationUrlValidator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class QrRedirectRuleController extends Controller
{
    public function __construct(
        private readonly QrRedirectCache $cache,
        private readonly DestinationUrlValidator $destinations,
    ) {}

    public function store(Request $request, QrCode $qrCode): RedirectResponse
    {
        $this->authorize('update', $qrCode);
        abort_unless($qrCode->isDynamic(), 422);

        $data = $request->validate([
            'type' => ['required', Rule::enum(RedirectRuleType::class)],
            'priority' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'destination_url' => ['nullable', 'string', 'max:2048'],
            'configuration' => ['required', 'array'],
        ]);

        if (filled($data['destination_url'] ?? null)) {
            $data['destination_url'] = $this->destinations->validate($data['destination_url']);
        }

        QrRedirectRule::create([
            'qr_code_id' => $qrCode->id,
            'type' => $data['type'],
            'operator' => 'equals',
            'configuration' => $data['configuration'],
            'destination_url' => $data['destination_url'] ?? null,
            'priority' => $data['priority'] ?? 100,
            'is_active' => true,
        ]);

        $this->cache->forget($qrCode->slug);
        $this->cache->put($qrCode->fresh('redirectRules'));

        return back()->with('success', 'Redirect rule added.');
    }

    public function destroy(QrCode $qrCode, QrRedirectRule $rule): RedirectResponse
    {
        $this->authorize('update', $qrCode);
        abort_unless($rule->qr_code_id === $qrCode->id, 404);

        $rule->delete();
        $this->cache->forget($qrCode->slug);
        $this->cache->put($qrCode->fresh('redirectRules'));

        return back()->with('success', 'Redirect rule removed.');
    }
}
