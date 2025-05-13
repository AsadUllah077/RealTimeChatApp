<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UnreadMessages implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $sender_id, $receiver_id, $count;
    /**
     * Create a new event instance.
     */
    public function __construct($sender_id, $receiver_id, $count)
    {
        $this->sender_id = $sender_id;
        $this->receiver_id = $receiver_id;
        $this->count = $count;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('unread-channel.' . $this->receiver_id),
        ];
    }

    public function BroadcastWith()
    {
        return [
            'senderId' => $this->sender_id,
            'receiverId' => $this->receiver_id,
            'count' => $this->count,
        ];
    }
}
