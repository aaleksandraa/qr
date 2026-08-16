<?php

namespace App\Services\Qr;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use InvalidArgumentException;

class QrImageGenerator
{
    public function __construct(private readonly QrDesignValidator $designValidator) {}

    /**
     * @param  array<string, mixed>  $design
     */
    public function svg(string $payload, array $design = [], int $size = 512): string
    {
        return $this->build($payload, $design, 'svg', $size);
    }

    /**
     * @param  array<string, mixed>  $design
     */
    public function png(string $payload, array $design = [], int $size = 1024): string
    {
        return $this->build($payload, $design, 'png', $size);
    }

    /**
     * @param  array<string, mixed>  $design
     */
    public function generate(string $payload, string $format, array $design = [], ?int $size = null): string
    {
        $format = strtolower($format);
        $size ??= $format === 'png'
            ? (int) config('qr.export.default_png_size', 1024)
            : (int) config('qr.export.default_svg_size', 512);

        return $this->build($payload, $design, $format, $size);
    }

    /**
     * @param  array<string, mixed>  $design
     */
    private function build(string $payload, array $design, string $format, int $size): string
    {
        if ($payload === '') {
            throw new InvalidArgumentException('QR payload cannot be empty.');
        }

        $design = $this->designValidator->normalize($design, $payload);
        $writer = $format === 'svg' ? new SvgWriter : new PngWriter;
        $fg = $this->toColor($design['foreground']);
        $bg = $this->toColor($design['background']);

        $builder = Builder::create()
            ->writer($writer)
            ->data($payload)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel($this->errorCorrection($design['error_correction']))
            ->size($size)
            ->margin(max((int) $design['quiet_zone'], 4) * (int) max(1, $size / 64))
            ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->foregroundColor($fg)
            ->backgroundColor($bg);

        if (filled($design['logo_path']) && is_string($design['logo_path']) && is_file($design['logo_path'])) {
            $logoWidth = (int) max(24, $size * (float) $design['logo_size']);
            $builder->logoPath($design['logo_path'])->logoResizeToWidth($logoWidth);
        }

        if (filled($design['cta_text'])) {
            $builder->labelText((string) $design['cta_text']);
        }

        return $builder->build()->getString();
    }

    private function errorCorrection(string $level): ErrorCorrectionLevel
    {
        return match (strtoupper($level)) {
            'L' => ErrorCorrectionLevel::Low,
            'Q' => ErrorCorrectionLevel::Quartile,
            'H' => ErrorCorrectionLevel::High,
            default => ErrorCorrectionLevel::Medium,
        };
    }

    private function toColor(string $hex): Color
    {
        $hex = ltrim($hex, '#');

        return new Color(
            (int) hexdec(substr($hex, 0, 2)),
            (int) hexdec(substr($hex, 2, 2)),
            (int) hexdec(substr($hex, 4, 2)),
        );
    }
}
