<?php

namespace App\Services\Redirect\Rules;

use App\Services\Redirect\RedirectContext;
use Carbon\CarbonImmutable;

class DateTimeRuleHandler implements RedirectRuleHandler
{
    public function type(): string
    {
        return 'datetime';
    }

    public function match(array $rule, RedirectContext $context): ?string
    {
        $config = $rule['configuration'] ?? [];
        $now = CarbonImmutable::now('UTC');

        $from = isset($config['from']) ? CarbonImmutable::parse($config['from'])->utc() : null;
        $until = isset($config['until']) ? CarbonImmutable::parse($config['until'])->utc() : null;

        if ($from && $now->lt($from)) {
            return null;
        }

        if ($until && $now->gte($until)) {
            return null;
        }

        return filled($rule['destination_url'] ?? null) ? (string) $rule['destination_url'] : null;
    }
}
