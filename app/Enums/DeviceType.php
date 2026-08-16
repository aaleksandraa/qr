<?php

namespace App\Enums;

enum DeviceType: string
{
    case Mobile = 'mobile';
    case Desktop = 'desktop';
    case Tablet = 'tablet';
    case Other = 'other';
}
