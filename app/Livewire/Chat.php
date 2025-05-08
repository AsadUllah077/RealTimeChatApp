<?php

namespace App\Livewire;

use App\Models\Message;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Chat extends Component
{
    public $userId, $user;
    public $sender_id, $reciever_id, $message='';
    public $messages=[];
    
    protected $rules = [
        'message' => 'required',
    ];

    public function mount($userId)
    {
        $this->userId = $userId;
        $this->user = User::find($userId);
        $this->sender_id = Auth::user()->id;
        $this->reciever_id = $userId;
        $this->messages = $this->getMessages();
    }

    public function render()
    {
        return view('livewire.chat');
    }

    public function sendMessage()
    {
        // dd("fsfsdfsdf");
        // $this->validate();
        $this->saveMessage();
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

    public function getMessages(){
        $this->messages=Message::with('sender', 'reciever')
        ->where(function($query){
            $query->where('sender_id', $this->sender_id)
            ->where('reciever_id', $this->reciever_id);
        })
        ->orWhere(function($query){
            $query->where('sender_id', $this->reciever_id)
            ->where('reciever_id', $this->sender_id);
        })->get();

        return $this->messages;
    }

    
}