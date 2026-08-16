<?php

namespace App\Services\Workspace;

use App\Enums\WorkspaceRole;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WorkspaceService
{
    public function provisionDefaultWorkspace(User $user): Workspace
    {
        return DB::transaction(function () use ($user) {
            $existing = $user->workspaces()->first();
            if ($existing) {
                return $existing;
            }

            $workspace = Workspace::create([
                'name' => $user->name."'s workspace",
                'slug' => Str::slug($user->name).'-'.Str::lower(Str::random(6)),
                'owner_id' => $user->id,
            ]);

            $workspace->addMember($user, WorkspaceRole::Owner);

            session(['current_workspace_id' => $workspace->id]);

            return $workspace;
        });
    }
}
