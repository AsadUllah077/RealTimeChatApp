<?php

namespace App\Events;

use App\Models\MessageReaction;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageReactionEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $reaction;

    public function __construct(MessageReaction $reaction)
    {
        $this->reaction = $reaction;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('chat.' . $this->reaction->message->reciever_id);
    }

    public function broadcastAs()
    {
        return 'MessageReactionEvent';
    }
}
