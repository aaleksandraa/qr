<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Tag extends Model
{
    protected $fillable = [
        'workspace_id',
        'name',
        'slug',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $tag): void {
            $tag->slug ??= Str::slug($tag->name);
        });
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function qrCodes(): BelongsToMany
    {
        return $this->belongsToMany(QrCode::class, 'qr_code_tag')->withTimestamps();
    }
}
