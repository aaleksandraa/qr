<?php

namespace App\Services\Qr\Payloads;

use App\Enums\StaticContentType;
use InvalidArgumentException;

class LocationPayloadBuilder implements PayloadBuilderInterface
{
    public function type(): StaticContentType
    {
        return StaticContentType::Location;
    }

    public function build(array $payload): string
    {
        $n = $this->normalize($payload);

        if ($n['latitude'] !== null && $n['longitude'] !== null) {
            return sprintf('geo:%s,%s', $n['latitude'], $n['longitude']);
        }

        if (filled($n['address'])) {
            return 'https://maps.google.com/?q='.rawurlencode($n['address']);
        }

        throw new InvalidArgumentException('Provide coordinates or an address.');
    }

    public function normalize(array $payload): array
    {
        $lat = $payload['latitude'] ?? null;
        $lng = $payload['longitude'] ?? null;

        return [
            'latitude' => is_numeric($lat) ? (float) $lat : null,
            'longitude' => is_numeric($lng) ? (float) $lng : null,
            'address' => filled($payload['address'] ?? null) ? trim((string) $payload['address']) : null,
        ];
    }
}
