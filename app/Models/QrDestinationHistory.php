<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QrDestinationHistory extends Model
{
    protected $table = 'qr_destination_history';

    protected $fillable = [
        'qr_code_id',
        'old_url',
        'new_url',
        'changed_by',
    ];

    public function qrCode(): BelongsTo
    {
        return $this->belongsTo(QrCode::class);
    }

    public function changer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
