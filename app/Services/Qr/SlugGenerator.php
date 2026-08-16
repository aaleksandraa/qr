<?php

namespace App\Services\Qr;

use App\Exceptions\ReservedSlug;
use App\Exceptions\SlugAlreadyExists;
use App\Models\QrCode;
use Illuminate\Support\Str;

class SlugGenerator
{
    private const ALPHABET = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    public function generateUnique(): string
    {
        $length = (int) config('qr.slug.length', 7);

        for ($attempt = 0; $attempt < 12; $attempt++) {
            $slug = $this->random($length + intdiv($attempt, 4));
            if (! $this->isReserved($slug) && ! $this->exists($slug)) {
                return $slug;
            }
        }

        throw new SlugAlreadyExists('Unable to generate a unique short URL. Please try again.');
    }

    public function normalizeCustom(string $slug): string
    {
        $slug = trim($slug);
        $min = (int) config('qr.slug.min_custom_length', 3);
        $max = (int) config('qr.slug.max_custom_length', 64);

        if (strlen($slug) < $min || strlen($slug) > $max) {
            throw new ReservedSlug("Custom short URLs must be between {$min} and {$max} characters.");
        }

        if (preg_match('/^[A-Za-z0-9][A-Za-z0-9_-]*$/', $slug) !== 1) {
            throw new ReservedSlug('Custom short URLs may only contain letters, numbers, hyphens, and underscores.');
        }

        if ($this->isReserved($slug)) {
            throw new ReservedSlug('This short URL is reserved.');
        }

        if ($this->exists($slug)) {
            throw new SlugAlreadyExists('This short URL is already in use.');
        }

        return $slug;
    }

    public function isReserved(string $slug): bool
    {
        $reserved = array_map('strtolower', config('qr.slug.reserved', []));

        return in_array(strtolower($slug), $reserved, true);
    }

    public function exists(string $slug): bool
    {
        return QrCode::withTrashed()->where('slug', $slug)->exists();
    }

    private function random(int $length): string
    {
        $max = strlen(self::ALPHABET) - 1;
        $slug = '';

        for ($i = 0; $i < $length; $i++) {
            $slug .= self::ALPHABET[random_int(0, $max)];
        }

        if (preg_match('/^\d+$/', $slug) === 1) {
            return $this->random($length);
        }

        return $slug;
    }
}
