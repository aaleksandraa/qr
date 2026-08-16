<?php

namespace App\Services\Qr\Payloads;

use App\Enums\StaticContentType;
use InvalidArgumentException;

class TextPayloadBuilder implements PayloadBuilderInterface
{
    public function type(): StaticContentType
    {
        return StaticContentType::Text;
    }

    public function build(array $payload): string
    {
        $normalized = $this->normalize($payload);
        $text = $normalized['text'];

        if ($text === '') {
            throw new InvalidArgumentException('Text content is required.');
        }

        $max = (int) config('qr.design.max_text_payload', 1200);
        if (mb_strlen($text) > $max) {
            throw new InvalidArgumentException("Text cannot exceed {$max} characters.");
        }

        return $text;
    }

    public function normalize(array $payload): array
    {
        return [
            'text' => trim((string) ($payload['text'] ?? '')),
        ];
    }
}
