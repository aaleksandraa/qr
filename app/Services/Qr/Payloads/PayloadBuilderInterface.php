<?php

namespace App\Services\Qr\Payloads;

use App\Enums\StaticContentType;

interface PayloadBuilderInterface
{
    public function type(): StaticContentType;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function build(array $payload): string;

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function normalize(array $payload): array;
}
