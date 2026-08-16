<?php

namespace App\Services\Redirect\Rules;

use App\Services\Redirect\RedirectContext;

interface RedirectRuleHandler
{
    public function type(): string;

    /**
     * @param  array<string, mixed>  $rule
     */
    public function match(array $rule, RedirectContext $context): ?string;
}
