<?php

namespace App\Services\Qr\Payloads;

use App\Enums\StaticContentType;
use App\Support\DestinationUrlValidator;
use Illuminate\Support\Arr;

class UrlPayloadBuilder implements PayloadBuilderInterface
{
    public function __construct(private readonly DestinationUrlValidator $validator) {}

    public function type(): StaticContentType
    {
        return StaticContentType::Url;
    }

    public function build(array $payload): string
    {
        $normalized = $this->normalize($payload);
        $url = $this->validator->validate($normalized['url']);

        $utm = array_filter([
            'utm_source' => $normalized['utm_source'] ?? null,
            'utm_medium' => $normalized['utm_medium'] ?? null,
            'utm_campaign' => $normalized['utm_campaign'] ?? null,
            'utm_content' => $normalized['utm_content'] ?? null,
            'utm_term' => $normalized['utm_term'] ?? null,
        ], fn ($value) => filled($value));

        if ($utm === []) {
            return $url;
        }

        $parts = parse_url($url);
        $existing = [];
        if (! empty($parts['query'])) {
            parse_str($parts['query'], $existing);
        }

        $query = http_build_query(array_merge($existing, $utm));

        $rebuilt = ($parts['scheme'] ?? 'https').'://'.($parts['host'] ?? '');
        if (! empty($parts['port'])) {
            $rebuilt .= ':'.$parts['port'];
        }
        $rebuilt .= $parts['path'] ?? '';
        $rebuilt .= '?'.$query;
        if (! empty($parts['fragment'])) {
            $rebuilt .= '#'.$parts['fragment'];
        }

        return $rebuilt;
    }

    public function normalize(array $payload): array
    {
        return [
            'url' => trim((string) Arr::get($payload, 'url', '')),
            'utm_source' => $this->nullableString($payload, 'utm_source'),
            'utm_medium' => $this->nullableString($payload, 'utm_medium'),
            'utm_campaign' => $this->nullableString($payload, 'utm_campaign'),
            'utm_content' => $this->nullableString($payload, 'utm_content'),
            'utm_term' => $this->nullableString($payload, 'utm_term'),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function nullableString(array $payload, string $key): ?string
    {
        $value = Arr::get($payload, $key);

        return filled($value) ? trim((string) $value) : null;
    }
}
