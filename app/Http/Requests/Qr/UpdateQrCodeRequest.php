<?php

namespace App\Http\Requests\Qr;

use App\Enums\StaticContentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateQrCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('qr_code')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:2000'],
            'content_type' => ['sometimes', Rule::enum(StaticContentType::class)],
            'payload' => ['sometimes', 'array'],
            'destination_url' => ['sometimes', 'nullable', 'string', 'max:2048'],
            'folder_id' => ['nullable', 'integer'],
            'campaign_id' => ['nullable', 'integer'],
            'tag_ids' => ['nullable', 'array'],
            'tracking_enabled' => ['sometimes', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
            'max_scans' => ['nullable', 'integer', 'min:1'],
            'password' => ['nullable', 'string', 'min:4', 'max:64'],
            'remove_password' => ['sometimes', 'boolean'],
            'fallback_url' => ['nullable', 'string', 'max:2048'],
            'expired_behavior' => ['nullable', 'in:page,fallback'],
            'utm' => ['nullable', 'array'],
            'design' => ['nullable', 'array'],
            'logo' => ['nullable', 'file', 'max:2048', 'mimes:png,jpg,jpeg,webp'],
        ];
    }
}
