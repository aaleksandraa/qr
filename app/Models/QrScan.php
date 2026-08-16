<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QrScan extends Model
{
    protected $fillable = [
        'qr_code_id',
        'scanned_at',
        'visitor_hash',
        'country_code',
        'country_name',
        'region',
        'city',
        'device_type',
        'os',
        'browser',
        'referrer',
        'user_agent_summary',
        'is_bot',
        'ab_variant',
        'request_id',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'scanned_at' => 'datetime',
            'is_bot' => 'boolean',
        ];
    }

    public function qrCode(): BelongsTo
    {
        return $this->belongsTo(QrCode::class);
    }
}
