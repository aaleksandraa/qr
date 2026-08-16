<?php

namespace App\Support;

use App\Exceptions\InvalidQrDestination;

class DestinationUrlValidator
{
    public function validate(string $url): string
    {
        $url = trim($url);

        if ($url === '') {
            throw new InvalidQrDestination('Destination URL is required.');
        }

        $parts = parse_url($url);

        if ($parts === false || empty($parts['scheme']) || empty($parts['host'])) {
            throw new InvalidQrDestination('Destination must be a valid HTTP or HTTPS URL.');
        }

        $scheme = strtolower($parts['scheme']);

        if (in_array($scheme, config('qr.forbidden_destination_schemes'), true)) {
            throw new InvalidQrDestination('This URL scheme is not allowed.');
        }

        if (! in_array($scheme, config('qr.allowed_destination_schemes'), true)) {
            throw new InvalidQrDestination('Destination must use HTTP or HTTPS.');
        }

        $host = strtolower($parts['host']);

        if (in_array($host, ['localhost', '127.0.0.1', '0.0.0.0', '::1'], true) && ! app()->environment(['local', 'testing'])) {
            throw new InvalidQrDestination('Local destinations are not allowed.');
        }

        $blocklist = config('qr.domain_blocklist', []);
        if ($blocklist !== [] && $this->hostMatchesList($host, $blocklist)) {
            throw new InvalidQrDestination('This destination domain is blocked.');
        }

        $allowlist = config('qr.domain_allowlist', []);
        if ($allowlist !== [] && ! $this->hostMatchesList($host, $allowlist)) {
            throw new InvalidQrDestination('This destination domain is not on the allowlist.');
        }

        return $url;
    }

    /**
     * @param  list<string>  $list
     */
    private function hostMatchesList(string $host, array $list): bool
    {
        foreach ($list as $domain) {
            $domain = strtolower(trim($domain));
            if ($domain === '') {
                continue;
            }
            if ($host === $domain || str_ends_with($host, '.'.$domain)) {
                return true;
            }
        }

        return false;
    }
}
