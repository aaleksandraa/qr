<?php

namespace App\Services\Qr\Payloads;

use App\Enums\StaticContentType;
use InvalidArgumentException;

class WifiPayloadBuilder implements PayloadBuilderInterface
{
    public function type(): StaticContentType
    {
        return StaticContentType::Wifi;
    }

    public function build(array $payload): string
    {
        $normalized = $this->normalize($payload);

        if ($normalized['ssid'] === '') {
            throw new InvalidArgumentException('Wi-Fi SSID is required.');
        }

        $security = $normalized['security'];
        if (! in_array($security, ['WPA', 'WEP', 'nopass'], true)) {
            throw new InvalidArgumentException('Unsupported Wi-Fi security type.');
        }

        if ($security !== 'nopass' && blank($normalized['password'])) {
            throw new InvalidArgumentException('A password is required for this Wi-Fi security type.');
        }

        $parts = [
            'T:'.$security,
            'S:'.$this->escape($normalized['ssid']),
        ];

        if ($security !== 'nopass' && filled($normalized['password'])) {
            $parts[] = 'P:'.$this->escape($normalized['password']);
        }

        if ($normalized['hidden']) {
            $parts[] = 'H:true';
        }

        return 'WIFI:'.implode(';', $parts).';;';
    }

    public function normalize(array $payload): array
    {
        $security = strtoupper((string) ($payload['security'] ?? 'WPA'));

        if (in_array($security, ['WPA2', 'WPA3', 'WPA/WPA2', 'WPA/WPA2/WPA3'], true)) {
            $security = 'WPA';
        }

        if (in_array($security, ['NONE', 'OPEN', 'NOPASS'], true)) {
            $security = 'nopass';
        }

        return [
            'ssid' => trim((string) ($payload['ssid'] ?? '')),
            'security' => $security,
            'password' => isset($payload['password']) ? (string) $payload['password'] : null,
            'hidden' => (bool) ($payload['hidden'] ?? false),
        ];
    }

    private function escape(string $value): string
    {
        return str_replace(
            ['\\', ';', ',', ':', '"'],
            ['\\\\', '\;', '\,', '\:', '\"'],
            $value,
        );
    }
}
