<?php

namespace App\Services\Qr\Payloads;

use App\Enums\StaticContentType;
use InvalidArgumentException;

class SmsPayloadBuilder implements PayloadBuilderInterface
{
    public function type(): StaticContentType
    {
        return StaticContentType::Sms;
    }

    public function build(array $payload): string
    {
        $normalized = $this->normalize($payload);

        if ($normalized['phone'] === '') {
            throw new InvalidArgumentException('A phone number is required.');
        }

        $sms = 'SMSTO:'.$normalized['phone'];

        if (filled($normalized['message'])) {
            $sms .= ':'.$normalized['message'];
        }

        return $sms;
    }

    public function normalize(array $payload): array
    {
        $raw = trim((string) ($payload['phone'] ?? ''));
        $phone = preg_replace('/[^\d+]/', '', $raw) ?? '';

        return [
            'phone' => $phone,
            'message' => filled($payload['message'] ?? null) ? (string) $payload['message'] : null,
        ];
    }
}
