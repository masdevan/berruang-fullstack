<?php

namespace App\Events;

use App\Models\Workspace;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WorkspaceMembersChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Workspace $workspace) {}

    public function broadcastOn(): array
    {
        return $this->workspace->activeMembers()
            ->pluck('users.id')
            ->map(fn (int $id) => new PrivateChannel('App.Models.User.'.$id))
            ->all();
    }

    public function broadcastWith(): array
    {
        return ['workspace_code' => $this->workspace->code];
    }
}
