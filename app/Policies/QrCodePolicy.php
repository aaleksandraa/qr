<?php

namespace App\Policies;

use App\Models\QrCode;
use App\Models\User;

class QrCodePolicy
{
    public function view(User $user, QrCode $qrCode): bool
    {
        return $user->belongsToWorkspace($qrCode->workspace);
    }

    public function update(User $user, QrCode $qrCode): bool
    {
        return $user->belongsToWorkspace($qrCode->workspace);
    }

    public function delete(User $user, QrCode $qrCode): bool
    {
        return $user->belongsToWorkspace($qrCode->workspace);
    }
}
