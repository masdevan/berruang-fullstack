<?php

namespace App\Events;

use App\Models\Workspace;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class WorkspaceTyping implements ShouldBroadcastNow
{
    use InteractsWithSockets, SerializesModels;

    public function __construct(
        public Workspace $workspace,
        public int $senderId,
        public string $username,
        public string $name,
        public bool $typing,
    ) {}

    public function broadcastOn(): array
    {
        return $this->workspace->activeMembers()
            ->where('users.id', '!=', $this->senderId)
            ->pluck('users.id')
            ->map(fn (int $id) => new PrivateChannel('App.Models.User.'.$id))
            ->all();
    }

    public function broadcastWith(): array
    {
        return [
            'workspace_code' => $this->workspace->code,
            'username' => $this->username,
            'name' => $this->name,
            'typing' => $this->typing,
        ];
    }
}
