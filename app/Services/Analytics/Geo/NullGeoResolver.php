<?php

namespace App\Services\Analytics\Geo;

use Illuminate\Http\Request;

class NullGeoResolver implements GeoResolverInterface
{
    public function resolve(Request $request, ?string $ip): array
    {
        return [
            'country_code' => null,
            'country_name' => null,
            'region' => null,
            'city' => null,
        ];
    }
}
