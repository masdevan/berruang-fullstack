<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class Workspace extends Model
{
    protected $fillable = [
        'code',
        'name',
        'bio',
        'avatar',
        'owner_id',
    ];

    public function avatarPreviewUrl(): string
    {
        if (! $this->avatar) {
            return '';
        }

        $preview = pathinfo($this->avatar, PATHINFO_DIRNAME).'/'
            .pathinfo($this->avatar, PATHINFO_FILENAME).'.preview.webp';

        return Storage::disk('public')->exists($preview)
            ? asset('storage/'.$preview)
            : asset('storage/'.$this->avatar);
    }

    public function avatarFullUrl(): string
    {
        return $this->avatar ? asset('storage/'.$this->avatar) : '';
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'workspace_user')->withPivot('role')->withTimestamps();
    }
}
