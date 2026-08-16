<?php

namespace App\Services\Qr;

use App\Exceptions\UnsafeQrDesign;

class QrDesignValidator
{
    /**
     * @param  array<string, mixed>  $design
     * @return array<string, mixed>
     */
    public function normalize(array $design, string $payload): array
    {
        $foreground = $this->normalizeHex($design['foreground'] ?? '#111827', '#111827');
        $background = $this->normalizeHex($design['background'] ?? '#FFFFFF', '#FFFFFF');
        $errorCorrection = strtoupper((string) ($design['error_correction'] ?? config('qr.default_error_correction', 'M')));
        $quietZone = max((int) config('qr.design.min_quiet_zone', 4), (int) ($design['quiet_zone'] ?? 4));
        $logoSize = min((float) ($design['logo_size'] ?? 0.16), (float) config('qr.design.max_logo_ratio', 0.22));
        $hasLogo = filled($design['logo_path'] ?? null);

        if (! in_array($errorCorrection, ['L', 'M', 'Q', 'H'], true)) {
            $errorCorrection = 'M';
        }

        if ($hasLogo && $errorCorrection !== 'H') {
            $errorCorrection = 'H';
        }

        $contrast = $this->contrastRatio($foreground, $background);
        $minContrast = (float) config('qr.design.min_contrast_ratio', 1.8);

        if ($contrast < $minContrast) {
            throw new UnsafeQrDesign('Foreground and background colors do not have enough contrast to scan reliably.');
        }

        if ($this->isTransparent($foreground)) {
            throw new UnsafeQrDesign('Transparent foreground colors are not allowed.');
        }

        $warnings = [];
        if ($contrast < (float) config('qr.design.warn_contrast_ratio', 3.0)) {
            $warnings[] = 'Low contrast may make this QR harder to scan.';
        }

        $denseAt = (int) config('qr.design.dense_payload_warning', 400);
        if (strlen($payload) >= $denseAt) {
            $warnings[] = 'This payload is large. The QR will be denser and harder to scan from a distance.';
        }

        if ($hasLogo && $logoSize > 0.18) {
            $warnings[] = 'A large center logo reduces scan reliability. Keep logos small.';
        }

        return [
            'foreground' => $foreground,
            'background' => $background,
            'error_correction' => $errorCorrection,
            'quiet_zone' => $quietZone,
            'module_style' => in_array($design['module_style'] ?? 'square', ['square', 'rounded'], true)
                ? ($design['module_style'] ?? 'square')
                : 'square',
            'logo_path' => $design['logo_path'] ?? null,
            'logo_size' => $logoSize,
            'cta_text' => filled($design['cta_text'] ?? null) ? mb_substr((string) $design['cta_text'], 0, 40) : null,
            'warnings' => $warnings,
        ];
    }

    public function contrastRatio(string $hexA, string $hexB): float
    {
        $l1 = $this->relativeLuminance($hexA);
        $l2 = $this->relativeLuminance($hexB);
        $lighter = max($l1, $l2);
        $darker = min($l1, $l2);

        return ($lighter + 0.05) / ($darker + 0.05);
    }

    private function normalizeHex(string $value, string $fallback): string
    {
        $value = trim($value);
        if (preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $value) !== 1) {
            return $fallback;
        }

        if (strlen($value) === 4) {
            return sprintf('#%s%s%s', $value[1], $value[1], $value[2], $value[2], $value[3], $value[3]);
        }

        return strtoupper($value);
    }

    private function isTransparent(string $hex): bool
    {
        return in_array(strtolower($hex), ['#00000000', 'transparent'], true);
    }

    private function relativeLuminance(string $hex): float
    {
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;

        $channel = static function (float $c): float {
            return $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
        };

        return 0.2126 * $channel($r) + 0.7152 * $channel($g) + 0.0722 * $channel($b);
    }
}
