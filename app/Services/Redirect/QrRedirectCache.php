<?php

namespace App\Services\Redirect;

use App\Models\QrCode;
use Illuminate\Support\Facades\Cache;

class QrRedirectCache
{
    /**
     * @return array<string, mixed>|null
     */
    public function get(string $slug): ?array
    {
        $cached = Cache::get($this->key($slug));

        return is_array($cached) ? $cached : null;
    }

    public function put(QrCode $qr): void
    {
        if (! $qr->isDynamic() || blank($qr->slug)) {
            return;
        }

        $qr->loadMissing('redirectRules');

        Cache::put($this->key($qr->slug), $this->serialize($qr), (int) config('qr.cache.ttl', 3600));
    }

    public function forget(?string $slug): void
    {
        if (filled($slug)) {
            Cache::forget($this->key($slug));
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function serialize(QrCode $qr): array
    {
        return [
            'id' => $qr->id,
            'public_id' => $qr->public_id,
            'workspace_id' => $qr->workspace_id,
            'slug' => $qr->slug,
            'status' => $qr->status->value,
            'destination_url' => $qr->destination_url,
            'fallback_url' => $qr->fallback_url,
            'expired_behavior' => $qr->expired_behavior,
            'starts_at' => $qr->starts_at?->toIso8601String(),
            'expires_at' => $qr->expires_at?->toIso8601String(),
            'max_scans' => $qr->max_scans,
            'total_scans' => $qr->total_scans,
            'tracking_enabled' => $qr->tracking_enabled,
            'password_protected' => $qr->isPasswordProtected(),
            'utm_parameters' => $qr->utm_parameters ?? [],
            'rules' => $qr->redirectRules
                ->where('is_active', true)
                ->sortBy('priority')
                ->values()
                ->map(fn ($rule) => [
                    'type' => $rule->type->value,
                    'operator' => $rule->operator,
                    'configuration' => $rule->configuration,
                    'destination_url' => $rule->destination_url,
                    'priority' => $rule->priority,
                ])
                ->all(),
        ];
    }

    private function key(string $slug): string
    {
        return config('qr.cache.redirect_prefix', 'qr:redirect:').$slug;
    }
}
