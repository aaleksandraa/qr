<?php

namespace App\Enums;

enum QrStatus: string
{
    case Active = 'active';
    case Paused = 'paused';
    case Archived = 'archived';
}
