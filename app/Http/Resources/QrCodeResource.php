<?php

namespace App\Http\Resources;

use App\Models\QrCode;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin QrCode */
class QrCodeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->public_id,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->qr_type->value,
            'content_type' => $this->content_type?->value,
            'status' => $this->status->value,
            'slug' => $this->slug,
            'short_url' => $this->shortUrl(),
            'destination_url' => $this->destination_url,
            'encoded_payload' => $this->encoded_payload,
            'static_payload' => $this->static_payload,
            'tracking_enabled' => $this->tracking_enabled,
            'starts_at' => $this->starts_at?->toIso8601String(),
            'expires_at' => $this->expires_at?->toIso8601String(),
            'max_scans' => $this->max_scans,
            'password_protected' => $this->isPasswordProtected(),
            'fallback_url' => $this->fallback_url,
            'expired_behavior' => $this->expired_behavior,
            'utm_parameters' => $this->utm_parameters,
            'design_config' => $this->when(
                true,
                fn () => collect($this->design_config ?? [])->except(['logo_path'])->all()
            ),
            'total_scans' => $this->when($this->isDynamic(), $this->total_scans),
            'human_scans' => $this->when($this->isDynamic(), $this->human_scans),
            'bot_scans' => $this->when($this->isDynamic(), $this->bot_scans),
            'estimated_unique_scans' => $this->when($this->isDynamic(), $this->estimated_unique_scans),
            'last_scanned_at' => $this->last_scanned_at?->toIso8601String(),
            'campaign' => $this->whenLoaded('campaign', fn () => $this->campaign ? [
                'id' => $this->campaign->public_id,
                'name' => $this->campaign->name,
            ] : null),
            'folder' => $this->whenLoaded('folder', fn () => $this->folder ? [
                'id' => $this->folder->public_id,
                'name' => $this->folder->name,
            ] : null),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
