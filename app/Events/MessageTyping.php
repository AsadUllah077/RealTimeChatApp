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

class MessageTyping implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $senderId, $recieverId;
    public $group_id;
    /**
     * Create a new event instance.
     */
    public function __construct($senderId, $recieverId = null, $group_id = null)
    {
        $this->senderId = $senderId;
        $this->recieverId = $recieverId;
        $this->group_id = $group_id;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
         if ($this->group_id) {
            // return new PrivateChannel('group-chat.' . $this->group_id);
            return [
                new PrivateChannel('chat-channel.'.$this->group_id),
            ];
        }
        return [
            new PrivateChannel('chat-channel.'.$this->recieverId),
        ];
    }
    public function BroadcastWith(){
        return [
            'senderId' => $this->senderId,
        ];
    }
}
