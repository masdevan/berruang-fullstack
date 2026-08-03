<?php

namespace App\Events;

use App\Models\Message;
use App\Services\ChatService;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Message $message) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('App.Models.User.'.$this->message->receiver_id)];
    }

    public function broadcastWith(): array
    {
        $viewer = $this->message->receiver;
        $sender = $this->message->sender;

        return [
            'id' => $this->message->id,
            'body' => $this->message->body,
            'time' => $this->message->created_at->format('H:i'),
            'from' => 'other',
            'type' => $this->message->type,
            'file' => $this->message->file_path
                ? [
                    'url' => $this->message->fileUrl(),
                    'preview_url' => app(ChatService::class)->previewUrl($this->message),
                    'name' => $this->message->fileName(),
                    'width' => $this->message->width,
                    'height' => $this->message->height,
                ]
                : null,
            'sender_user_id' => $sender->id,
            'sender_username' => $sender->username,
            'sender_avatar' => $sender->avatar ? $sender->avatarUrl(36) : $sender->initials(),
            'sender_full_avatar' => $sender->avatar ? $sender->avatarFullUrl() : null,
            'sender_has_avatar' => (bool) $sender->avatar,
            'sender_bio' => $sender->bio ?? '',
            ...$this->message->senderDisplayFor($viewer),
        ];
    }
}
