<?php

namespace App\Enums;

enum QrCodeType: string
{
    case Static = 'static';
    case Dynamic = 'dynamic';

    public function label(): string
    {
        return match ($this) {
            self::Static => 'Static QR',
            self::Dynamic => 'Dynamic QR',
        };
    }
}
