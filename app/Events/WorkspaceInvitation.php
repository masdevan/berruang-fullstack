<?php

namespace App\Events;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WorkspaceInvitation implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Workspace $workspace,
        public User $invitee,
        public User $inviter,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('App.Models.User.'.$this->invitee->id)];
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->workspace->id,
            'name' => $this->workspace->name,
            'code' => $this->workspace->code,
            'created' => $this->workspace->created_at->format('d M Y'),
            'avatar' => $this->workspace->avatar ? $this->workspace->avatarPreviewUrl() : '',
            'full_avatar' => $this->workspace->avatar ? $this->workspace->avatarFullUrl() : '',
            'bio' => $this->workspace->bio ?? '',
            'inviter_name' => $this->inviter->name,
        ];
    }
}
