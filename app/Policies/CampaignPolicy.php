<?php

namespace App\Policies;

use App\Models\Campaign;
use App\Models\User;

class CampaignPolicy
{
    public function view(User $user, Campaign $campaign): bool
    {
        return $user->belongsToWorkspace($campaign->workspace);
    }

    public function update(User $user, Campaign $campaign): bool
    {
        return $user->belongsToWorkspace($campaign->workspace);
    }

    public function delete(User $user, Campaign $campaign): bool
    {
        return $user->belongsToWorkspace($campaign->workspace);
    }
}
