<?php

namespace App\Enums;

enum StaticContentType: string
{
    case Url = 'url';
    case Text = 'text';
    case Email = 'email';
    case Phone = 'phone';
    case Sms = 'sms';
    case Wifi = 'wifi';
    case Vcard = 'vcard';
    case Location = 'location';
}
