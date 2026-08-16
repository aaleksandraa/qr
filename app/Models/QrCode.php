<?php

namespace App\Models;

use App\Enums\QrCodeType;
use App\Enums\QrStatus;
use App\Enums\StaticContentType;
use Database\Factories\QrCodeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class QrCode extends Model
{
    /** @use HasFactory<QrCodeFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'public_id',
        'workspace_id',
        'folder_id',
        'campaign_id',
        'created_by',
        'custom_domain_id',
        'name',
        'description',
        'qr_type',
        'content_type',
        'slug',
        'destination_url',
        'static_payload',
        'encoded_payload',
        'status',
        'tracking_enabled',
        'starts_at',
        'expires_at',
        'max_scans',
        'password_hash',
        'fallback_url',
        'expired_behavior',
        'utm_parameters',
        'design_config',
        'total_scans',
        'human_scans',
        'bot_scans',
        'estimated_unique_scans',
        'last_scanned_at',
    ];

    protected function casts(): array
    {
        return [
            'qr_type' => QrCodeType::class,
            'content_type' => StaticContentType::class,
            'status' => QrStatus::class,
            'static_payload' => 'array',
            'utm_parameters' => 'array',
            'design_config' => 'array',
            'tracking_enabled' => 'boolean',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'last_scanned_at' => 'datetime',
            'max_scans' => 'integer',
            'total_scans' => 'integer',
            'human_scans' => 'integer',
            'bot_scans' => 'integer',
            'estimated_unique_scans' => 'integer',
        ];
    }

    protected $hidden = [
        'password_hash',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $qrCode): void {
            $qrCode->public_id ??= (string) Str::ulid();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function customDomain(): BelongsTo
    {
        return $this->belongsTo(CustomDomain::class);
    }

    public function destinationHistory(): HasMany
    {
        return $this->hasMany(QrDestinationHistory::class)->latest();
    }

    public function redirectRules(): HasMany
    {
        return $this->hasMany(QrRedirectRule::class)->orderBy('priority');
    }

    public function scans(): HasMany
    {
        return $this->hasMany(QrScan::class);
    }

    public function dailyStats(): HasMany
    {
        return $this->hasMany(QrScanDailyStat::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'qr_code_tag')->withTimestamps();
    }

    public function isStatic(): bool
    {
        return $this->qr_type === QrCodeType::Static;
    }

    public function isDynamic(): bool
    {
        return $this->qr_type === QrCodeType::Dynamic;
    }

    public function isPasswordProtected(): bool
    {
        return filled($this->password_hash);
    }

    public function shortUrl(): ?string
    {
        if (! $this->isDynamic() || blank($this->slug)) {
            return null;
        }

        return rtrim((string) config('qr.short_base_url'), '/').'/'.$this->slug;
    }

    public function scopeInWorkspace(Builder $query, Workspace $workspace): Builder
    {
        return $query->where('workspace_id', $workspace->id);
    }

    public function scopeDynamic(Builder $query): Builder
    {
        return $query->where('qr_type', QrCodeType::Dynamic);
    }

    public function scopeStatic(Builder $query): Builder
    {
        return $query->where('qr_type', QrCodeType::Static);
    }
}
