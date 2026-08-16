<?php

namespace App\Http\Requests\Qr;

use App\Enums\QrCodeType;
use App\Enums\StaticContentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQrCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $nullable = ['custom_slug', 'folder_id', 'campaign_id', 'starts_at', 'expires_at', 'max_scans', 'password', 'fallback_url'];
        $merge = [];
        foreach ($nullable as $key) {
            if ($this->input($key) === '') {
                $merge[$key] = null;
            }
        }
        if ($merge !== []) {
            $this->merge($merge);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'qr_type' => ['required', Rule::enum(QrCodeType::class)],
            'name' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:2000'],
            'content_type' => ['required_if:qr_type,static', Rule::enum(StaticContentType::class)],
            'payload' => ['required_if:qr_type,static', 'array'],
            'destination_url' => ['required_if:qr_type,dynamic', 'nullable', 'string', 'max:2048'],
            'custom_slug' => ['nullable', 'string', 'max:64'],
            'folder_id' => ['nullable', 'integer'],
            'campaign_id' => ['nullable', 'integer'],
            'tag_ids' => ['nullable', 'array'],
            'tracking_enabled' => ['sometimes', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after:starts_at'],
            'max_scans' => ['nullable', 'integer', 'min:1'],
            'password' => ['nullable', 'string', 'min:4', 'max:64'],
            'fallback_url' => ['nullable', 'string', 'max:2048'],
            'expired_behavior' => ['nullable', 'in:page,fallback'],
            'utm' => ['nullable', 'array'],
            'design' => ['nullable', 'array'],
            'design.foreground' => ['nullable', 'string', 'max:9'],
            'design.background' => ['nullable', 'string', 'max:9'],
            'design.error_correction' => ['nullable', 'in:L,M,Q,H'],
            'design.quiet_zone' => ['nullable', 'integer', 'min:4', 'max:16'],
            'design.cta_text' => ['nullable', 'string', 'max:40'],
            'logo' => ['nullable', 'file', 'max:2048', 'mimes:png,jpg,jpeg,webp'],
        ];
    }
}
