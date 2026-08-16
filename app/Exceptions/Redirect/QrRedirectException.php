<?php

namespace App\Exceptions\Redirect;

use RuntimeException;

class QrRedirectException extends RuntimeException
{
    public function __construct(
        public readonly string $reason,
        public readonly ?string $fallbackUrl = null,
        string $message = '',
    ) {
        parent::__construct($message !== '' ? $message : $reason);
    }
}
