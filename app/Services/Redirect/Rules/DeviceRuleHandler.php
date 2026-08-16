<?php

namespace App\Services\Redirect\Rules;

use App\Services\Redirect\RedirectContext;

class DeviceRuleHandler implements RedirectRuleHandler
{
    public function type(): string
    {
        return 'device';
    }

    public function match(array $rule, RedirectContext $context): ?string
    {
        $config = $rule['configuration'] ?? [];
        $map = $config['destinations'] ?? [];

        if (! is_array($map)) {
            return null;
        }

        $os = strtolower((string) $context->osFamily);
        $device = strtolower((string) $context->deviceFamily);

        foreach ($map as $key => $url) {
            $needle = strtolower((string) $key);
            if ($needle !== '' && ($os === $needle || $device === $needle || str_contains($os, $needle))) {
                return filled($url) ? (string) $url : null;
            }
        }

        return filled($rule['destination_url'] ?? null) ? (string) $rule['destination_url'] : null;
    }
}
