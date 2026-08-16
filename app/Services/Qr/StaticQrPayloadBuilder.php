<?php

namespace App\Services\Qr;

use App\Enums\StaticContentType;
use App\Services\Qr\Payloads\PayloadBuilderInterface;
use InvalidArgumentException;

class StaticQrPayloadBuilder
{
    /** @var array<string, PayloadBuilderInterface> */
    private array $builders = [];

    /**
     * @param  iterable<PayloadBuilderInterface>  $builders
     */
    public function __construct(iterable $builders)
    {
        foreach ($builders as $builder) {
            $this->builders[$builder->type()->value] = $builder;
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function build(StaticContentType $type, array $payload): string
    {
        return $this->builder($type)->build($payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function normalize(StaticContentType $type, array $payload): array
    {
        return $this->builder($type)->normalize($payload);
    }

    private function builder(StaticContentType $type): PayloadBuilderInterface
    {
        if (! isset($this->builders[$type->value])) {
            throw new InvalidArgumentException("Unsupported static content type [{$type->value}].");
        }

        return $this->builders[$type->value];
    }
}
