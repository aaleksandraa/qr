<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QrScanDailyStat extends Model
{
    protected $fillable = [
        'qr_code_id',
        'date',
        'total_scans',
        'human_scans',
        'bot_scans',
        'unique_scans',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'total_scans' => 'integer',
            'human_scans' => 'integer',
            'bot_scans' => 'integer',
            'unique_scans' => 'integer',
        ];
    }

    public function qrCode(): BelongsTo
    {
        return $this->belongsTo(QrCode::class);
    }
}
