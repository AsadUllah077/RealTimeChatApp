<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use App\Events\MessageSendEvent;
use App\Events\MessageTyping;
use App\Events\UnreadMessages;
use App\Events\MessageReactionEvent;
use App\Events\MessageSeenEvent;
use App\Models\Message;
use App\Models\MessageReaction;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class Chat extends Component
{
    use WithFileUploads;

    public $userId, $user;
    public $sender_id, $reciever_id, $message = '', $file;
    public $messages = [], $new_messages = [];
    public $audioBlob;
    public $audioBlobUrl;
    public $isRecording = false;
    public $selectedMessage = null;
    public $reactionTypes = ['👍', '❤️', '😂', '😮', '😢', '🙏'];
    public $isTyping = false;

    public function mount($userId)
    {
        $this->userId = $userId;
        $this->user = User::findOrFail($userId);
        $this->sender_id = Auth::id();
        $this->reciever_id = $userId;
        $this->messages = $this->getMessages();
        $this->markAsReadMessages();
        $this->dispatch('message-load');
    }

    public function render()
    {
        $this->markAsReadMessages();
        return view('livewire.chat');
    }

    public function startRecording()
    {
        $this->isRecording = true;
        $this->dispatch('start-recording');
    }

    public function stopRecording()
    {
        $this->isRecording = false;
        $this->dispatch('stop-recording');
    }

    public function clearRecording()
    {
        $this->audioBlob = null;
        $this->audioBlobUrl = null;
    }

    #[On('voice-recorded')]
    public function handleVoiceRecorded($audioBlob)
    {
        $this->audioBlob = $audioBlob;
        $this->audioBlobUrl = 'data:audio/wav;base64,' . $audioBlob;
    }

    public function sendVoiceMessage()
    {
        if (!$this->audioBlob) {
            return;
        }

        $audioPath = 'voice_messages/' . uniqid() . '.wav';
        Storage::disk('public')->put($audioPath, base64_decode($this->audioBlob));

        $sentMessage = Message::create([
            'sender_id' => $this->sender_id,
            'reciever_id' => $this->reciever_id,
            'audio_path' => $audioPath,
            'is_read' => false,
        ]);

        // Broadcast the message
        broadcast(new MessageSendEvent($sentMessage))->toOthers();
        $countUnread = $this->unReadCount();
        broadcast(new UnreadMessages($this->sender_id, $this->reciever_id, $countUnread))->toOthers();

        // Add message to current user's messages
        $this->messages[] = $sentMessage->toArray();
        $this->clearRecording();

        $this->dispatch('message-load-send');
    }

    public function sendMessage()
    {
        if ($this->audioBlob) {
            return $this->sendVoiceMessage();
        }

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
        $this->messages = $this->getMessages();
        $this->dispatch('message-load-send');
    }

    public function markAsReadMessages()
    {
        $unreadMessages = Message::where('sender_id', $this->reciever_id)
            ->where('reciever_id', $this->sender_id)
            ->where('is_read', false)
            ->get();

        foreach ($unreadMessages as $message) {
            $message->update(['is_read' => true]);
            broadcast(new MessageSeenEvent($message, $this->sender_id))->toOthers();
        }
    }

    public function userTyping()
    {
        $this->isTyping = true;
        broadcast(new MessageTyping($this->sender_id, $this->reciever_id))->toOthers();
        $this->dispatch('typing-timeout');
    }

    #[On('typing-timeout')]
    public function typingTimeout()
    {
        sleep(2);
        $this->isTyping = false;
    }
    public function saveMessage()
    {
        $fileName = $this->file ? $this->file->hashName() : null;
        $fileOriginalName = $this->file ? $this->file->getClientOriginalName() : null;
        $filePath = $this->file ? $this->file->store('chat_files', 'public') : null;
        $fileType = $this->file ? $this->file->getMimeType() : null;
// dd($this->file);
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
    #[On('user-typing')]
    public function handleUserTyping($userId)
    {
        if ($userId == $this->reciever_id) {
            $this->isTyping = true;
            $this->dispatch('clear-typing');
        }
    }

    #[On('clear-typing')]
    public function clearTyping()
    {
        sleep(2);
        $this->isTyping = false;
    }

    public function getMessages()
    {
        return Message::with(['sender:id,name', 'reactions.user'])
            ->where(function ($query) {
                $query->where('sender_id', $this->sender_id)
                    ->where('reciever_id', $this->reciever_id);
            })
            ->orWhere(function ($query) {
                $query->where('sender_id', $this->reciever_id)
                    ->where('reciever_id', $this->sender_id);
            })
            ->orderBy('created_at')
            ->get()
            ->map(function($message) {
                $message->is_seen = $message->is_read;
                return $message;
            })
            ->toArray();
    }
    // #[On('add-reaction')]
    public function addReaction($messageId, $reaction)
    {
        dd("fsfsd");
        $message = Message::findOrFail($messageId);


        // Check if user already reacted with this reaction
        $existingReaction = MessageReaction::where('message_id', $messageId)
            ->where('user_id', auth()->id())
            ->where('reaction', $reaction)
            ->first();

        if ($existingReaction) {
            // Remove reaction if same reaction clicked again
            $existingReaction->delete();
        } else {
            // Remove any existing reaction from this user
            MessageReaction::where('message_id', $messageId)
                ->where('user_id', auth()->id())
                ->delete();

            // Add new reaction
            $newReaction = MessageReaction::create([
                'message_id' => $messageId,
                'user_id' => auth()->id(),
                'reaction' => $reaction,
            ]);

            broadcast(new MessageReactionEvent($newReaction))->toOthers();
        }

        $this->messages = $this->getMessages();
    }
    public function reactToMessage($messageId, $reaction)
    {
        $message = Message::findOrFail($messageId);

        // Check if user already reacted with this reaction
        $existingReaction = MessageReaction::where('message_id', $messageId)
            ->where('user_id', $this->sender_id)
            ->where('reaction', $reaction)
            ->first();

        if ($existingReaction) {
            // Remove reaction if same reaction clicked again
            $existingReaction->delete();
        } else {
            // Remove any existing reaction from this user
            MessageReaction::where('message_id', $messageId)
                ->where('user_id', $this->sender_id)
                ->delete();

            // Add new reaction
            $newReaction = MessageReaction::create([
                'message_id' => $messageId,
                'user_id' => $this->sender_id,
                'reaction' => $reaction,
            ]);

            broadcast(new MessageReactionEvent($newReaction))->toOthers();
        }

        $this->messages = $this->getMessages();
    }
    public function selectMessage($messageId)
    {
        $this->selectedMessage = $messageId;
    }

}
