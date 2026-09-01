<?php

namespace App\Events;

use App\Models\WorkspaceMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WorkspaceMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public WorkspaceMessage $message) {}

    public function broadcastOn(): array
    {
        return $this->message->workspace->activeMembers()
            ->where('users.id', '!=', $this->message->sender_id)
            ->pluck('users.id')
            ->map(fn (int $id) => new PrivateChannel('App.Models.User.'.$id))
            ->all();
    }

    public function broadcastWith(): array
    {
        $sender = $this->message->sender;

        return [
            'workspace_code' => $this->message->workspace->code,
            'id' => $this->message->id,
            'body' => $this->message->body,
            'time' => $this->message->created_at->format('H:i'),
            'type' => $this->message->type,
            'sender_user_id' => $sender->id,
            'sender_name' => $sender->name,
            'sender_username' => $sender->username,
            'sender_avatar' => $sender->avatar ? $sender->avatarUrl(36) : $sender->initials(),
            'sender_has_avatar' => (bool) $sender->avatar,
            'file' => $this->message->file_path
                ? [
                    'url' => $this->message->fileUrl(),
                    'preview_url' => $this->message->preview_path ? asset('storage/'.$this->message->preview_path) : null,
                    'name' => $this->message->fileName(),
                    'width' => $this->message->width,
                    'height' => $this->message->height,
                ]
                : null,
        ];
    }
}
