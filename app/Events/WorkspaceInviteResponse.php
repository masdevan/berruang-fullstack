<?php

namespace App\Events;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WorkspaceInviteResponse implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Workspace $workspace,
        public User $user,
        public bool $accepted,
    ) {}

    public function broadcastOn(): array
    {
        return $this->workspace->members()
            ->wherePivotIn('role', ['owner', 'admin'])
            ->wherePivot('status', 'member')
            ->pluck('users.id')
            ->map(fn (int $id) => new PrivateChannel('App.Models.User.'.$id))
            ->all();
    }

    public function broadcastWith(): array
    {
        return [
            'workspace_code' => $this->workspace->code,
            'accepted' => $this->accepted,
            'user_id' => $this->user->id,
            'name' => $this->user->name,
            'username' => $this->user->username,
            'avatar' => $this->user->avatar ? $this->user->avatarUrl(36) : $this->user->initials(),
            'has_avatar' => (bool) $this->user->avatar,
        ];
    }
}
