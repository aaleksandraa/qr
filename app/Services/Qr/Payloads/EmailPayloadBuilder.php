<?php

namespace App\Services\Qr\Payloads;

use App\Enums\StaticContentType;
use InvalidArgumentException;

class EmailPayloadBuilder implements PayloadBuilderInterface
{
    public function type(): StaticContentType
    {
        return StaticContentType::Email;
    }

    public function build(array $payload): string
    {
        $normalized = $this->normalize($payload);

        if (! filter_var($normalized['email'], FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('A valid email address is required.');
        }

        $query = array_filter([
            'subject' => $normalized['subject'],
            'body' => $normalized['body'],
        ], fn ($value) => filled($value));

        $mailto = 'mailto:'.$normalized['email'];

        if ($query !== []) {
            $mailto .= '?'.http_build_query($query, '', '&', PHP_QUERY_RFC3986);
        }

        return $mailto;
    }

    public function normalize(array $payload): array
    {
        return [
            'email' => trim((string) ($payload['email'] ?? '')),
            'subject' => filled($payload['subject'] ?? null) ? trim((string) $payload['subject']) : null,
            'body' => filled($payload['body'] ?? null) ? trim((string) $payload['body']) : null,
        ];
    }
}
