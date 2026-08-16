<?php

namespace App\Services\Qr\Payloads;

use App\Enums\StaticContentType;
use InvalidArgumentException;

class VCardPayloadBuilder implements PayloadBuilderInterface
{
    public function type(): StaticContentType
    {
        return StaticContentType::Vcard;
    }

    public function build(array $payload): string
    {
        $n = $this->normalize($payload);

        if ($n['first_name'] === '' && $n['last_name'] === '' && $n['company'] === '') {
            throw new InvalidArgumentException('A name or company is required for a contact QR.');
        }

        $lines = [
            'BEGIN:VCARD',
            'VERSION:3.0',
            'N:'.$this->escape($n['last_name']).';'.$this->escape($n['first_name']).';;;',
            'FN:'.$this->escape(trim($n['first_name'].' '.$n['last_name']) ?: $n['company']),
        ];

        if (filled($n['company'])) {
            $lines[] = 'ORG:'.$this->escape($n['company']);
        }
        if (filled($n['job_title'])) {
            $lines[] = 'TITLE:'.$this->escape($n['job_title']);
        }
        if (filled($n['phone'])) {
            $lines[] = 'TEL;TYPE=WORK,VOICE:'.$this->escape($n['phone']);
        }
        if (filled($n['mobile'])) {
            $lines[] = 'TEL;TYPE=CELL:'.$this->escape($n['mobile']);
        }
        if (filled($n['email'])) {
            $lines[] = 'EMAIL;TYPE=INTERNET:'.$this->escape($n['email']);
        }
        if (filled($n['website'])) {
            $lines[] = 'URL:'.$this->escape($n['website']);
        }

        $address = array_filter([
            $n['street'],
            $n['city'],
            $n['postal_code'],
            $n['country'],
        ], fn ($value) => filled($value));

        if ($address !== []) {
            $lines[] = 'ADR;TYPE=WORK:;;'
                .$this->escape((string) $n['street']).';'
                .$this->escape((string) $n['city']).';;'
                .$this->escape((string) $n['postal_code']).';'
                .$this->escape((string) $n['country']);
        }

        if (filled($n['note'])) {
            $lines[] = 'NOTE:'.$this->escape($n['note']);
        }

        $lines[] = 'END:VCARD';

        return implode("\n", $lines);
    }

    public function normalize(array $payload): array
    {
        $keys = [
            'first_name', 'last_name', 'company', 'job_title', 'phone', 'mobile',
            'email', 'website', 'street', 'city', 'postal_code', 'country', 'note',
        ];

        $normalized = [];
        foreach ($keys as $key) {
            $value = $payload[$key] ?? null;
            $normalized[$key] = filled($value) ? trim((string) $value) : '';
        }

        return $normalized;
    }

    private function escape(string $value): string
    {
        return str_replace(["\\", ";", ",", "\n"], ['\\\\', '\;', '\,', '\\n'], $value);
    }
}
