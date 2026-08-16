<?php

namespace App\Services\Redirect\Rules;

use App\Services\Redirect\RedirectContext;

class LanguageRuleHandler implements RedirectRuleHandler
{
    public function type(): string
    {
        return 'language';
    }

    public function match(array $rule, RedirectContext $context): ?string
    {
        $header = strtolower((string) $context->acceptLanguage);
        if ($header === '') {
            return null;
        }

        $map = $rule['configuration']['destinations'] ?? [];
        if (! is_array($map)) {
            return null;
        }

        foreach ($map as $lang => $url) {
            $lang = strtolower((string) $lang);
            if ($lang !== '' && (str_starts_with($header, $lang) || str_contains($header, $lang)) && filled($url)) {
                return (string) $url;
            }
        }

        return null;
    }
}
