<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use App\Events\GroupMessageSendEvent;
use App\Events\MessageTyping;
use App\Events\MessageReactionEvent;
use App\Events\MessageSeenEvent;
use App\Models\Message;
use App\Models\GroupChat  as GroupChatModel;
use App\Models\MessageReaction;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class GroupChat extends Component
{
    use WithFileUploads;

    public $groupId;
    public $group;
    public $sender_id;
    public $message = '';
    public $file;
    public $messages = [];
    public $audioBlob;
    public $audioBlobUrl;
    public $isRecording = false;
    public $selectedMessage = null;
    public $reactionTypes = ['👍', '❤️', '😂', '😮', '😢', '🙏'];
    public $typingUsers = [];

    public function mount($groupId)
    {
        $this->groupId = $groupId;
        $this->group = GroupChatModel::with(['members', 'creator'])->findOrFail($groupId);
        $this->sender_id = Auth::id();
        $this->messages = $this->getMessages();
        $this->markAsReadMessages();
        $this->dispatch('message-load');
    }

    public function render()
    {
        $this->markAsReadMessages();
        return view('livewire.group-chat')->layout('layouts.app');
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
            'group_id' => $this->groupId,
            'is_group' => true,
            'audio_path' => $audioPath,
        ]);

        broadcast(new GroupMessageSendEvent($sentMessage))->toOthers();

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
        broadcast(new GroupMessageSendEvent($sentMessage))->toOthers();

        $this->messages[] = $sentMessage;
        $this->message = '';
        $this->file = '';
        $this->dispatch('message-load-send');
        $this->dispatch('message-sent');
    }

    #[On('message-received')]
    public function handleReceivedMessage($message)
    {
        $this->messages = $this->getMessages();
        $this->dispatch('message-load-send');
    }

    public function markAsReadMessages()
    {
        $unreadMessages = Message::where('group_id', $this->groupId)
            ->where('sender_id', '!=', $this->sender_id)
            ->whereDoesntHave('seenBy', function($query) {
                $query->where('user_id', $this->sender_id);
            })
            ->get();

        foreach ($unreadMessages as $message) {
            $message->seenBy()->attach($this->sender_id);
            broadcast(new MessageSeenEvent($message, $this->sender_id))->toOthers();
        }
    }

    public function userTyping()
    {
        broadcast(new MessageTyping($this->sender_id, null, $this->groupId))->toOthers();
    }

    #[On('user-typing')]
    public function handleUserTyping($userId, $groupId)
    {
        if ($groupId == $this->groupId) {
            $user = User::find($userId);
            if ($user && !in_array($user->name, $this->typingUsers)) {
                $this->typingUsers[] = $user->name;
            }

            $this->dispatch('typing-indicator');

            // Clear after 2 seconds
            $this->dispatch('clear-typing', userId: $userId);
        }
    }

    #[On('clear-typing')]
    public function clearTyping($userId)
    {
        if (($key = array_search(User::find($userId)->name, $this->typingUsers)) !== false) {
            unset($this->typingUsers[$key]);
        }
    }

    public function saveMessage()
    {
        $fileName = $this->file ? $this->file->hashName() : null;
        $fileOriginalName = $this->file ? $this->file->getClientOriginalName() : null;
        $filePath = $this->file ? $this->file->store('chat_files', 'public') : null;
        $fileType = $this->file ? $this->file->getMimeType() : null;

        return Message::create([
            'sender_id' => $this->sender_id,
            'group_id' => $this->groupId,
            'is_group' => true,
            'message' => $this->message,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'file_original_name' => $fileOriginalName,
            'file_type' => $fileType,
        ]);
    }

    public function getMessages()
    {
        return Message::with(['sender:id,name', 'reactions.user', 'seenBy:id,name'])
            ->where('group_id', $this->groupId)
            ->orderBy('created_at')
            ->get()
            ->map(function($message) {
                $message->is_seen = $message->isSeenBy(Auth::id());
                return $message;
            })
            ->toArray();
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
