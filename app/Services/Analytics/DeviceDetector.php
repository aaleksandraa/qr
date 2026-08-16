<?php

namespace App\Services\Analytics;

class DeviceDetector
{
    /**
     * @return array{device_type: string, os: string, browser: string, summary: string}
     */
    public function detect(?string $userAgent): array
    {
        $ua = (string) $userAgent;

        return [
            'device_type' => $this->deviceType($ua),
            'os' => $this->os($ua),
            'browser' => $this->browser($ua),
            'summary' => $this->summary($ua),
        ];
    }

    public function osFamily(?string $userAgent): string
    {
        return $this->os((string) $userAgent);
    }

    public function deviceFamily(?string $userAgent): string
    {
        return $this->deviceType((string) $userAgent);
    }

    private function deviceType(string $ua): string
    {
        if (preg_match('/iPad|Tablet|PlayBook|Silk/i', $ua)) {
            return 'tablet';
        }

        if (preg_match('/Mobile|iPhone|Android.*Mobile|Windows Phone/i', $ua)) {
            return 'mobile';
        }

        if ($ua === '') {
            return 'other';
        }

        return 'desktop';
    }

    private function os(string $ua): string
    {
        return match (true) {
            str_contains($ua, 'iPhone') || str_contains($ua, 'iPad') || str_contains($ua, 'iOS') => 'iOS',
            str_contains($ua, 'Android') => 'Android',
            str_contains($ua, 'Windows') => 'Windows',
            str_contains($ua, 'Mac OS') || str_contains($ua, 'Macintosh') => 'macOS',
            str_contains($ua, 'Linux') => 'Linux',
            default => 'Other',
        };
    }

    private function browser(string $ua): string
    {
        return match (true) {
            str_contains($ua, 'Edg/') || str_contains($ua, 'Edge/') => 'Edge',
            str_contains($ua, 'SamsungBrowser') => 'Samsung Internet',
            str_contains($ua, 'Firefox/') => 'Firefox',
            str_contains($ua, 'Chrome/') && ! str_contains($ua, 'Edg') => 'Chrome',
            str_contains($ua, 'Safari/') && ! str_contains($ua, 'Chrome') => 'Safari',
            default => 'Other',
        };
    }

    private function summary(string $ua): string
    {
        $trimmed = trim(preg_replace('/\s+/', ' ', $ua) ?? '');

        return mb_substr($trimmed, 0, 255);
    }
}
