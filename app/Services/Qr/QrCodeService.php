<?php

namespace App\Services\Qr;

use App\Enums\QrCodeType;
use App\Enums\QrStatus;
use App\Enums\StaticContentType;
use App\Models\QrCode;
use App\Models\QrDestinationHistory;
use App\Models\User;
use App\Models\Workspace;
use App\Services\Audit\AuditLogger;
use App\Services\Redirect\QrRedirectCache;
use App\Exceptions\InvalidQrDestination;
use App\Exceptions\ReservedSlug;
use App\Exceptions\SlugAlreadyExists;
use App\Exceptions\UnsafeQrDesign;
use App\Support\DestinationUrlValidator;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class QrCodeService
{
    public function __construct(
        private readonly StaticQrPayloadBuilder $staticBuilder,
        private readonly SlugGenerator $slugGenerator,
        private readonly QrDesignValidator $designValidator,
        private readonly DestinationUrlValidator $destinationValidator,
        private readonly QrRedirectCache $redirectCache,
        private readonly AuditLogger $auditLogger,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(Workspace $workspace, User $user, array $data): QrCode
    {
        $type = QrCodeType::from($data['qr_type']);

        try {
            return DB::transaction(function () use ($workspace, $user, $data, $type) {
            $design = $this->storeLogo($workspace, $data['design'] ?? [], $data['logo'] ?? null);

            if ($type === QrCodeType::Static) {
                $contentType = StaticContentType::from($data['content_type']);
                $payload = $this->staticBuilder->normalize($contentType, $data['payload'] ?? []);
                $encoded = $this->staticBuilder->build($contentType, $payload);
                $design = $this->designValidator->normalize($design, $encoded);

                $qr = QrCode::create([
                    'workspace_id' => $workspace->id,
                    'created_by' => $user->id,
                    'folder_id' => $data['folder_id'] ?? null,
                    'campaign_id' => $data['campaign_id'] ?? null,
                    'name' => $data['name'],
                    'description' => $data['description'] ?? null,
                    'qr_type' => $type,
                    'content_type' => $contentType,
                    'static_payload' => $payload,
                    'encoded_payload' => $encoded,
                    'status' => QrStatus::Active,
                    'tracking_enabled' => false,
                    'design_config' => $design,
                ]);
            } else {
                $destination = $this->destinationValidator->validate((string) $data['destination_url']);
                try {
                    $slug = filled($data['custom_slug'] ?? null)
                        ? $this->slugGenerator->normalizeCustom((string) $data['custom_slug'])
                        : $this->slugGenerator->generateUnique();
                } catch (ReservedSlug|SlugAlreadyExists $e) {
                    throw ValidationException::withMessages([
                        'custom_slug' => $e->getMessage(),
                    ]);
                }

                $shortUrl = rtrim((string) config('qr.short_base_url'), '/').'/'.$slug;
                $design = $this->designValidator->normalize($design, $shortUrl);

                $qr = QrCode::create([
                    'workspace_id' => $workspace->id,
                    'created_by' => $user->id,
                    'folder_id' => $data['folder_id'] ?? null,
                    'campaign_id' => $data['campaign_id'] ?? null,
                    'name' => $data['name'],
                    'description' => $data['description'] ?? null,
                    'qr_type' => $type,
                    'content_type' => null,
                    'slug' => $slug,
                    'destination_url' => $destination,
                    'encoded_payload' => $shortUrl,
                    'status' => QrStatus::Active,
                    'tracking_enabled' => (bool) ($data['tracking_enabled'] ?? true),
                    'starts_at' => $data['starts_at'] ?? null,
                    'expires_at' => $data['expires_at'] ?? null,
                    'max_scans' => $data['max_scans'] ?? null,
                    'password_hash' => filled($data['password'] ?? null) ? Hash::make((string) $data['password']) : null,
                    'fallback_url' => filled($data['fallback_url'] ?? null)
                        ? $this->destinationValidator->validate((string) $data['fallback_url'])
                        : null,
                    'expired_behavior' => $data['expired_behavior'] ?? 'page',
                    'utm_parameters' => $this->normalizeUtm($data['utm'] ?? []),
                    'design_config' => $design,
                ]);

                $this->redirectCache->put($qr);
            }

            if (! empty($data['tag_ids']) && is_array($data['tag_ids'])) {
                $qr->tags()->sync($data['tag_ids']);
            }

            $this->auditLogger->log($qr, 'created', null, [
                'qr_type' => $qr->qr_type->value,
                'name' => $qr->name,
            ], $user);

            return $qr->fresh(['campaign', 'folder', 'tags']) ?? $qr;
        });
        } catch (InvalidQrDestination $e) {
            throw ValidationException::withMessages([
                'destination_url' => $e->getMessage(),
            ]);
        } catch (UnsafeQrDesign $e) {
            throw ValidationException::withMessages([
                'design' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(QrCode $qr, User $user, array $data): QrCode
    {
        return DB::transaction(function () use ($qr, $user, $data) {
            $old = $qr->only([
                'name', 'description', 'destination_url', 'status', 'folder_id',
                'campaign_id', 'starts_at', 'expires_at', 'max_scans', 'fallback_url',
            ]);

            $qr->name = $data['name'] ?? $qr->name;
            $qr->description = array_key_exists('description', $data) ? $data['description'] : $qr->description;
            $qr->folder_id = array_key_exists('folder_id', $data) ? $data['folder_id'] : $qr->folder_id;
            $qr->campaign_id = array_key_exists('campaign_id', $data) ? $data['campaign_id'] : $qr->campaign_id;

            if ($qr->isStatic() && isset($data['payload'], $data['content_type'])) {
                $contentType = StaticContentType::from($data['content_type']);
                $payload = $this->staticBuilder->normalize($contentType, $data['payload']);
                $encoded = $this->staticBuilder->build($contentType, $payload);
                $qr->content_type = $contentType;
                $qr->static_payload = $payload;
                $qr->encoded_payload = $encoded;
            }

            if ($qr->isDynamic()) {
                if (array_key_exists('destination_url', $data) && $data['destination_url'] !== $qr->destination_url) {
                    $newUrl = $this->destinationValidator->validate((string) $data['destination_url']);
                    QrDestinationHistory::create([
                        'qr_code_id' => $qr->id,
                        'old_url' => $qr->destination_url,
                        'new_url' => $newUrl,
                        'changed_by' => $user->id,
                    ]);
                    $qr->destination_url = $newUrl;
                }

                foreach (['starts_at', 'expires_at', 'max_scans', 'expired_behavior', 'tracking_enabled'] as $field) {
                    if (array_key_exists($field, $data)) {
                        $qr->{$field} = $data[$field];
                    }
                }

                if (array_key_exists('fallback_url', $data)) {
                    $qr->fallback_url = filled($data['fallback_url'])
                        ? $this->destinationValidator->validate((string) $data['fallback_url'])
                        : null;
                }

                if (array_key_exists('utm', $data)) {
                    $qr->utm_parameters = $this->normalizeUtm($data['utm'] ?? []);
                }

                if (array_key_exists('password', $data)) {
                    $qr->password_hash = filled($data['password']) ? Hash::make((string) $data['password']) : $qr->password_hash;
                }

                if (array_key_exists('remove_password', $data) && $data['remove_password']) {
                    $qr->password_hash = null;
                }
            }

            if (isset($data['design']) || isset($data['logo'])) {
                $design = $this->storeLogo($qr->workspace, array_merge($qr->design_config ?? [], $data['design'] ?? []), $data['logo'] ?? null);
                $qr->design_config = $this->designValidator->normalize($design, $qr->encoded_payload);
            }

            $qr->save();

            if (isset($data['tag_ids']) && is_array($data['tag_ids'])) {
                $qr->tags()->sync($data['tag_ids']);
            }

            if ($qr->isDynamic()) {
                $this->redirectCache->forget($qr->slug);
                $this->redirectCache->put($qr->fresh());
            }

            $this->auditLogger->log($qr, 'updated', $old, $qr->only(array_keys($old)), $user);

            return $qr->fresh(['campaign', 'folder', 'tags']) ?? $qr;
        });
    }

    public function changeStatus(QrCode $qr, QrStatus $status, User $user): QrCode
    {
        $old = $qr->status;
        $qr->status = $status;
        $qr->save();

        if ($qr->isDynamic() && $qr->slug) {
            $this->redirectCache->forget($qr->slug);
            $this->redirectCache->put($qr->fresh());
        }

        $this->auditLogger->log($qr, 'status_changed', ['status' => $old?->value], ['status' => $status->value], $user);

        return $qr;
    }

    public function duplicate(QrCode $qr, User $user): QrCode
    {
        $data = [
            'qr_type' => $qr->qr_type->value,
            'name' => $qr->name.' (copy)',
            'description' => $qr->description,
            'folder_id' => $qr->folder_id,
            'campaign_id' => $qr->campaign_id,
            'content_type' => $qr->content_type?->value,
            'payload' => $qr->static_payload ?? [],
            'destination_url' => $qr->destination_url,
            'tracking_enabled' => $qr->tracking_enabled,
            'starts_at' => $qr->starts_at,
            'expires_at' => $qr->expires_at,
            'max_scans' => $qr->max_scans,
            'fallback_url' => $qr->fallback_url,
            'expired_behavior' => $qr->expired_behavior,
            'utm' => $qr->utm_parameters ?? [],
            'design' => $qr->design_config ?? [],
            'tag_ids' => $qr->tags()->pluck('tags.id')->all(),
        ];

        return $this->create($qr->workspace, $user, $data);
    }

    public function convertToDynamic(QrCode $qr, User $user): QrCode
    {
        if (! $qr->isStatic()) {
            throw new InvalidArgumentException('Only Static QR codes can be converted to Dynamic.');
        }

        $destination = $qr->content_type === StaticContentType::Url
            ? ($qr->encoded_payload ?: ($qr->static_payload['url'] ?? null))
            : null;

        if (! $destination || ! str_starts_with($destination, 'http')) {
            throw new InvalidArgumentException('Only Static URL QR codes can be converted to a Dynamic destination.');
        }

        return $this->create($qr->workspace, $user, [
            'qr_type' => QrCodeType::Dynamic->value,
            'name' => $qr->name.' (dynamic)',
            'description' => $qr->description,
            'destination_url' => $destination,
            'folder_id' => $qr->folder_id,
            'campaign_id' => $qr->campaign_id,
            'design' => $qr->design_config ?? [],
        ]);
    }

    public function convertToStatic(QrCode $qr, User $user): QrCode
    {
        if (! $qr->isDynamic() || blank($qr->destination_url)) {
            throw new InvalidArgumentException('Only Dynamic QR codes with a destination can be converted to Static.');
        }

        return $this->create($qr->workspace, $user, [
            'qr_type' => QrCodeType::Static->value,
            'content_type' => StaticContentType::Url->value,
            'name' => $qr->name.' (static)',
            'description' => $qr->description,
            'payload' => ['url' => $qr->destination_url],
            'folder_id' => $qr->folder_id,
            'campaign_id' => $qr->campaign_id,
            'design' => $qr->design_config ?? [],
        ]);
    }

    /**
     * @param  array<string, mixed>  $design
     * @return array<string, mixed>
     */
    private function storeLogo(Workspace $workspace, array $design, mixed $logo): array
    {
        if (! $logo instanceof UploadedFile) {
            return $design;
        }

        $extension = strtolower($logo->getClientOriginalExtension());
        $mime = (string) $logo->getMimeType();

        if ($extension === 'svg' || str_contains($mime, 'svg')) {
            throw new InvalidArgumentException('SVG logo uploads are disabled until sanitization is enabled.');
        }

        if (! in_array($mime, ['image/png', 'image/jpeg', 'image/webp'], true)) {
            throw new InvalidArgumentException('Logo must be a PNG, JPEG, or WEBP image.');
        }

        if ($logo->getSize() > 2 * 1024 * 1024) {
            throw new InvalidArgumentException('Logo must be smaller than 2 MB.');
        }

        $path = $logo->store("qr/logos/{$workspace->id}", 'local');
        $design['logo_path'] = Storage::disk('local')->path($path);

        return $design;
    }

    /**
     * @param  array<string, mixed>  $utm
     * @return array<string, string>
     */
    private function normalizeUtm(array $utm): array
    {
        return array_filter([
            'utm_source' => isset($utm['utm_source']) ? trim((string) $utm['utm_source']) : null,
            'utm_medium' => isset($utm['utm_medium']) ? trim((string) $utm['utm_medium']) : null,
            'utm_campaign' => isset($utm['utm_campaign']) ? trim((string) $utm['utm_campaign']) : null,
            'utm_content' => isset($utm['utm_content']) ? trim((string) $utm['utm_content']) : null,
            'utm_term' => isset($utm['utm_term']) ? trim((string) $utm['utm_term']) : null,
        ], fn ($value) => filled($value));
    }
}
