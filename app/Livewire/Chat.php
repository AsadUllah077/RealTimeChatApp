<?php

namespace App\Livewire;

use App\Events\MessageSendEvent;
use App\Events\MessageTyping;
use App\Events\UnreadMessages;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class Chat extends Component
{
    use WithFileUploads;
    public $userId, $user;
    public $sender_id, $reciever_id, $message = '',$file;

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
        $this->markAsReadMessages();

        // This will run after the component renders
        $this->dispatch('message-load');
    }

    public function render()
    {
        $this->markAsReadMessages();
        return view('livewire.chat');
    }

    public function sendMessage()
    {
        // $this->validate();

        $sentMessage = $this->saveMessage();

        // Broadcast the message
        broadcast(new MessageSendEvent($sentMessage))->toOthers();
        $countUnread = $this->unReadCount();
        broadcast(new UnreadMessages($this->sender_id, $this->reciever_id, $countUnread))->toOthers();

        // Add message to current user's messages
        $this->messages[] = $sentMessage;

        // Reset message input
        $this->message = '';
        $this->file = '';

        // Dispatch frontend event
        $this->dispatch('message-load-send');
        $this->dispatch('message-sent');

    }


    public function unReadCount(){
        return Message::where('reciever_id', $this->reciever_id)->where('is_read', false)->count();
    }

    #[On('message-received')]
    public function handleReceivedMessage($message)
    {
        // Add received message to messages list
        $this->messages = $this->getMessages();
        $this->dispatch('message-load-send');
        $this->dispatch('message-sent');
    }


    public function markAsReadMessages(){
        Message::where('sender_id', $this->reciever_id)->where('reciever_id', $this->sender_id)->where('is_read', false)->update([
            'is_read' =>true,
        ]);
    }


    public function userTyping(){
        broadcast( new MessageTyping($this->sender_id, $this->reciever_id))->toOthers();
    }

    public function saveMessage()
    {

        $fileName = $this->file ? $this->file->hashName() : null;
        $fileOriginalName = $this->file ? $this->file->getClientOriginalName() : null;
        $filePath = $this->file ? $this->file->store('chat_files', 'public') : null;
        $fileType = $this->file ? $this->file->getMimeType() : null;

        return Message::create([
            'sender_id' => $this->sender_id,
            'reciever_id' => $this->reciever_id,
            'message' => $this->message,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'file_original_name' => $fileOriginalName,
            'file_type' => $fileType,
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
