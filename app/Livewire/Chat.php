<?php

namespace App\Livewire;

use App\Events\MessageSendEvent;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class Chat extends Component
{
    public $userId, $user;
    public $sender_id, $reciever_id, $message = '';
    
    public $messages = [];
    public $new_messages = [];
    // protected $rules = [
    //     'message' => 'required|string|max:1000',
    // ];

    public function mount($userId)
    {
        $this->userId = $userId;
        $this->user = User::findOrFail($userId);
        $this->sender_id = Auth::id();
        $this->reciever_id = $userId;
        $this->messages = $this->getMessages();
    }

    public function render()
    {
        return view('livewire.chat');
    }

    public function sendMessage()
    {
        // $this->validate();
        
        $sentMessage = $this->saveMessage();
        
        // Broadcast the message
        broadcast(new MessageSendEvent($sentMessage))->toOthers();
        
        // Add message to current user's messages
        $this->messages[] = $sentMessage;
        
        // Reset message input
        $this->message = '';
        
        // Dispatch frontend event
        $this->dispatch('message-sent');
        $this->dispatch('message-load');

    }

    #[On('message-received')]
    public function handleReceivedMessage($message)
    {
        // Add received message to messages list
        $this->messages = $this->getMessages();
        $this->dispatch('message-load');
    }

    public function saveMessage()
    {
        return Message::create([
            'sender_id' => $this->sender_id,
            'reciever_id' => $this->reciever_id,
            'message' => $this->message,
            'is_read' => false,
        ]);
    }

    public function getMessages()
    {
        return Message::with('sender:id,name', 'reciever:id,name')
            ->where(function ($query) {
                
                $query->where('sender_id', $this->sender_id)
                    ->where('reciever_id', $this->reciever_id);
            })
            ->orWhere(function ($query) {
                $query->where('sender_id', $this->reciever_id)
                    ->where('reciever_id', $this->sender_id);
            })
            ->orderBy('created_at')
            ->get()->toArray();
    }
}