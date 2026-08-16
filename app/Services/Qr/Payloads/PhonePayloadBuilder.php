<?php

namespace App\Services\Qr\Payloads;

use App\Enums\StaticContentType;
use InvalidArgumentException;

class PhonePayloadBuilder implements PayloadBuilderInterface
{
    public function type(): StaticContentType
    {
        return StaticContentType::Phone;
    }

    public function build(array $payload): string
    {
        $normalized = $this->normalize($payload);

        if ($normalized['phone'] === '') {
            throw new InvalidArgumentException('A phone number is required.');
        }

        return 'tel:'.$normalized['phone'];
    }

    public function normalize(array $payload): array
    {
        $raw = trim((string) ($payload['phone'] ?? ''));
        $normalized = preg_replace('/[^\d+]/', '', $raw) ?? '';

        if ($normalized !== '' && ! str_starts_with($normalized, '+') && str_starts_with($raw, '+')) {
            $normalized = '+'.$normalized;
        }

        return [
            'phone' => $normalized,
        ];
    }
}
