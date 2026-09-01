<?php

namespace App\Events;

use App\Models\Workspace;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WorkspaceMessagesRead implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Workspace $workspace,
        public int $readerId,
        public int $lastReadMessageId,
    ) {}

    public function broadcastOn(): array
    {
        return $this->workspace->activeMembers()
            ->where('users.id', '!=', $this->readerId)
            ->pluck('users.id')
            ->map(fn (int $id) => new PrivateChannel('App.Models.User.'.$id))
            ->all();
    }

    public function broadcastWith(): array
    {
        return [
            'workspace_code' => $this->workspace->code,
            'reader_user_id' => $this->readerId,
            'last_read_message_id' => $this->lastReadMessageId,
        ];
    }
}
