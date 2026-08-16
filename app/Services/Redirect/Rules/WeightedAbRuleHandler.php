<?php

namespace App\Services\Redirect\Rules;

use App\Services\Redirect\RedirectContext;

class WeightedAbRuleHandler implements RedirectRuleHandler
{
    public function type(): string
    {
        return 'weighted_ab';
    }

    public function match(array $rule, RedirectContext $context): ?string
    {
        $variants = $rule['configuration']['variants'] ?? [];
        if (! is_array($variants) || $variants === []) {
            return null;
        }

        $total = 0;
        foreach ($variants as $variant) {
            $total += max(0, (int) ($variant['weight'] ?? 0));
        }

        if ($total <= 0) {
            return null;
        }

        $bucket = hexdec(substr(hash('sha256', $context->visitorHash.'|ab|'.($rule['priority'] ?? '0')), 0, 8)) % $total;
        $cursor = 0;

        foreach ($variants as $variant) {
            $cursor += max(0, (int) ($variant['weight'] ?? 0));
            if ($bucket < $cursor && filled($variant['url'] ?? null)) {
                return (string) $variant['url'];
            }
        }

        return null;
    }
}
