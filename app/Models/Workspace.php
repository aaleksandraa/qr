<?php

namespace App\Models;

use App\Enums\WorkspaceRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Workspace extends Model
{
    use HasFactory;

    protected $fillable = [
        'public_id',
        'name',
        'slug',
        'owner_id',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $workspace): void {
            $workspace->public_id ??= (string) Str::ulid();
            $workspace->slug ??= Str::slug($workspace->name).'-'.Str::lower(Str::random(6));
        });
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'workspace_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function qrCodes(): HasMany
    {
        return $this->hasMany(QrCode::class);
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    public function folders(): HasMany
    {
        return $this->hasMany(Folder::class);
    }

    public function tags(): HasMany
    {
        return $this->hasMany(Tag::class);
    }

    public function customDomains(): HasMany
    {
        return $this->hasMany(CustomDomain::class);
    }

    public function addMember(User $user, WorkspaceRole $role = WorkspaceRole::Member): void
    {
        $this->users()->syncWithoutDetaching([
            $user->id => ['role' => $role->value],
        ]);
    }
}
