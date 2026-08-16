<?php

namespace App\Models;

use App\Enums\RedirectRuleType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QrRedirectRule extends Model
{
    protected $fillable = [
        'qr_code_id',
        'type',
        'operator',
        'configuration',
        'destination_url',
        'priority',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type' => RedirectRuleType::class,
            'configuration' => 'array',
            'is_active' => 'boolean',
            'priority' => 'integer',
        ];
    }

    public function qrCode(): BelongsTo
    {
        return $this->belongsTo(QrCode::class);
    }
}
