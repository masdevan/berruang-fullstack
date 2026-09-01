<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkspaceMessage extends Model
{
    protected $fillable = [
        'workspace_id',
        'sender_id',
        'body',
        'type',
        'file_path',
        'preview_path',
        'width',
        'height',
    ];

    protected $casts = [
        'width' => 'integer',
        'height' => 'integer',
    ];

    public const TYPES = ['text', 'image', 'video', 'document'];

    public function fileUrl(): ?string
    {
        return $this->file_path ? asset('storage/'.$this->file_path) : null;
    }

    public function fileName(): string
    {
        return $this->file_path ? basename($this->file_path) : '';
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }
}
