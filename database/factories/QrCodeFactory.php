<?php

namespace Database\Factories;

use App\Enums\QrCodeType;
use App\Enums\QrStatus;
use App\Enums\StaticContentType;
use App\Models\QrCode;
use App\Models\User;
use App\Models\Workspace;
use App\Services\Qr\SlugGenerator;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QrCode>
 */
class QrCodeFactory extends Factory
{
    protected $model = QrCode::class;

    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'created_by' => User::factory(),
            'name' => fake()->words(3, true),
            'qr_type' => QrCodeType::Static,
            'content_type' => StaticContentType::Url,
            'static_payload' => ['url' => 'https://example.com/test'],
            'encoded_payload' => 'https://example.com/test',
            'status' => QrStatus::Active,
            'tracking_enabled' => false,
            'design_config' => [
                'foreground' => '#111827',
                'background' => '#FFFFFF',
                'error_correction' => 'M',
                'quiet_zone' => 4,
            ],
        ];
    }

    public function dynamic(?string $destination = 'https://example.com/page-a', ?string $slug = null): static
    {
        return $this->state(function () use ($destination, $slug) {
            $slug ??= app(SlugGenerator::class)->generateUnique();

            return [
                'qr_type' => QrCodeType::Dynamic,
                'content_type' => null,
                'static_payload' => null,
                'slug' => $slug,
                'destination_url' => $destination,
                'encoded_payload' => rtrim((string) config('qr.short_base_url'), '/').'/'.$slug,
                'tracking_enabled' => true,
            ];
        });
    }
}
