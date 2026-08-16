<?php

namespace App\Services\Redirect;

use Illuminate\Http\Request;

class RedirectContext
{
    public function __construct(
        public readonly Request $request,
        public readonly string $userAgent,
        public readonly ?string $acceptLanguage,
        public readonly ?string $countryCode,
        public readonly string $visitorHash,
        public readonly ?string $deviceFamily,
        public readonly ?string $osFamily,
    ) {}
}
