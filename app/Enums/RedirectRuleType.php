<?php

namespace App\Enums;

enum RedirectRuleType: string
{
    case Device = 'device';
    case Country = 'country';
    case Language = 'language';
    case DateTime = 'datetime';
    case WeightedAb = 'weighted_ab';
}
