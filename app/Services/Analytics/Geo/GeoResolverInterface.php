<?php

namespace App\Services\Analytics\Geo;

use Illuminate\Http\Request;

interface GeoResolverInterface
{
    /**
     * @return array{country_code: ?string, country_name: ?string, region: ?string, city: ?string}
     */
    public function resolve(Request $request, ?string $ip): array;
}
