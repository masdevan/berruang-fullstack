<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class TypingEvent implements ShouldBroadcastNow
{
    use SerializesModels;

    public function __construct(
        public int $toId,
        public string $fromUsername,
        public string $fromName,
        public bool $typing,
    ) {}

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('App.Models.User.'.$this->toId);
    }

    public function broadcastWith(): array
    {
        return [
            'from_username' => $this->fromUsername,
            'from_name' => $this->fromName,
            'typing' => $this->typing,
        ];
    }
}
