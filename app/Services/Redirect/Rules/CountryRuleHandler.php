<?php

namespace App\Services\Redirect\Rules;

use App\Services\Redirect\RedirectContext;

class CountryRuleHandler implements RedirectRuleHandler
{
    public function type(): string
    {
        return 'country';
    }

    public function match(array $rule, RedirectContext $context): ?string
    {
        if (! $context->countryCode) {
            return null;
        }

        $map = $rule['configuration']['destinations'] ?? [];
        if (! is_array($map)) {
            return null;
        }

        $code = strtoupper($context->countryCode);

        foreach ($map as $country => $url) {
            if (strtoupper((string) $country) === $code && filled($url)) {
                return (string) $url;
            }
        }

        return null;
    }
}
