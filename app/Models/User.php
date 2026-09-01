<?php

namespace App\Models;

use App\Services\EmailCodeService;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'username',
        'email',
        'password',
        'avatar',
        'avatar_preview_path',
        'username_changed_at',
        'bio',
        'onboarded_at',
    ];

    protected function getNameAttribute(): string
    {
        return trim(($this->attributes['first_name'] ?? '').' '.($this->attributes['last_name'] ?? ''));
    }

    protected function setNameAttribute($value): void
    {
        $parts = preg_split('/\s+/', trim((string) $value), 2);
        $this->attributes['first_name'] = $parts[0] ?? null;
        $this->attributes['last_name'] = $parts[1] ?? null;
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'username_changed_at' => 'datetime',
            'onboarded_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function sendEmailVerificationNotification(): bool
    {
        return app(EmailCodeService::class)->sendVerificationCode($this->email);
    }

    public function avatarUrl(int $size = 64): string
    {
        if ($this->avatar_preview_path) {
            return asset('storage/'.$this->avatar_preview_path);
        }

        if ($this->avatar) {
            return asset('storage/'.$this->avatar);
        }

        return 'https://ui-avatars.com/api/?name='.rawurlencode(Str::substr($this->name, 0, 1))."&background=2A2A2A&color=FFFFFF&size={$size}";
    }

    public function avatarFullUrl(): string
    {
        return $this->avatar ? asset('storage/'.$this->avatar) : $this->avatarUrl();
    }

    public function initials(): string
    {
        return collect(preg_split('/\s+/', trim($this->name)))
            ->take(2)
            ->map(fn (string $word) => Str::upper(Str::substr($word, 0, 1)))
            ->join('');
    }

    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'contacts', 'user_id', 'contact_user_id')
            ->withPivot(['first_name', 'last_name'])
            ->withTimestamps();
    }

    public function receivedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function workspaces(): BelongsToMany
    {
        return $this->belongsToMany(Workspace::class, 'workspace_user')
            ->using(WorkspaceUser::class)
            ->withPivot(['role', 'status', 'inviter_id', 'last_read_message_id'])
            ->withTimestamps();
    }
}
