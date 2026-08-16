<?php

namespace App\Services\Analytics\Geo;

use Illuminate\Http\Request;

class HeaderGeoResolver implements GeoResolverInterface
{
    public function resolve(Request $request, ?string $ip): array
    {
        return [
            'country_code' => $this->normalizeCountry($request->headers->get('CF-IPCountry') ?? $request->headers->get('X-Country-Code')),
            'country_name' => $request->headers->get('X-Country-Name'),
            'region' => $request->headers->get('X-Region') ?? $request->headers->get('CF-Region'),
            'city' => $request->headers->get('X-City') ?? $request->headers->get('CF-IPCity'),
        ];
    }

    private function normalizeCountry(?string $code): ?string
    {
        if (! $code || strtoupper($code) === 'XX') {
            return null;
        }

        $code = strtoupper($code);

        return strlen($code) === 2 ? $code : null;
    }
}
