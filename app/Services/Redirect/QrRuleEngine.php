<?php

namespace App\Services\Redirect;

use App\Services\Redirect\Rules\RedirectRuleHandler;

class QrRuleEngine
{
    /** @var array<string, RedirectRuleHandler> */
    private array $handlers = [];

    /**
     * @param  iterable<RedirectRuleHandler>  $handlers
     */
    public function __construct(iterable $handlers)
    {
        foreach ($handlers as $handler) {
            $this->handlers[$handler->type()] = $handler;
        }
    }

    /**
     * @param  array<string, mixed>  $cachedQr
     */
    public function resolve(array $cachedQr, RedirectContext $context): string
    {
        $rules = $cachedQr['rules'] ?? [];

        usort($rules, fn (array $a, array $b) => ($a['priority'] ?? 100) <=> ($b['priority'] ?? 100));

        foreach ($rules as $rule) {
            $type = $rule['type'] ?? null;
            $handler = is_string($type) ? ($this->handlers[$type] ?? null) : null;

            if (! $handler) {
                continue;
            }

            $destination = $handler->match($rule, $context);
            if (filled($destination)) {
                return $destination;
            }
        }

        return (string) $cachedQr['destination_url'];
    }
}
