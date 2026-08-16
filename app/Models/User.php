<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Enums\WorkspaceRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'timezone',
        'locale',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    public function workspaces(): BelongsToMany
    {
        return $this->belongsToMany(Workspace::class, 'workspace_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function ownedWorkspaces(): HasMany
    {
        return $this->hasMany(Workspace::class, 'owner_id');
    }

    public function currentWorkspace(): ?Workspace
    {
        $workspaceId = session('current_workspace_id');

        if ($workspaceId) {
            $workspace = $this->workspaces()->where('workspaces.id', $workspaceId)->first();
            if ($workspace) {
                return $workspace;
            }
        }

        return $this->workspaces()->first();
    }

    public function belongsToWorkspace(Workspace $workspace): bool
    {
        return $this->workspaces()->where('workspaces.id', $workspace->id)->exists();
    }

    public function workspaceRole(Workspace $workspace): ?WorkspaceRole
    {
        $role = $this->workspaces()->where('workspaces.id', $workspace->id)->first()?->pivot?->role;

        return $role ? WorkspaceRole::from($role) : null;
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }
}
