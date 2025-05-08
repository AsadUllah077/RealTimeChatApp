<?php

namespace App\Livewire;

use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Chat extends Component
{
    public $userId, $user;
    public $sender_id, $reciever_id, $message='';
    
    protected $rules = [
        'message' => 'required|string',
    ];

    public function mount($userId)
    {
        $this->userId = $userId;
        $this->user = User::find($userId);
        $this->sender_id = Auth::user()->id;
        $this->reciever_id = $userId;
    }

    public function render()
    {
        return view('livewire.chat');
    }

    public function sendMessage()
    {
        $this->validate();
        $this->message = '';
        $this->dispatch('message-sent');
    }

    public function saveMessage()
    {
        Message::create([
            'sender_id' => $this->sender_id,
            'reciever_id' => $this->reciever_id,
            'message' => $this->message,
            'is_read' => false,
        ]);
    }
}