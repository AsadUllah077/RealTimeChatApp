<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\GroupChat;
use App\Models\GroupChatUser;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class GroupChatList extends Component
{
    public $groups = [];
    public $search = '';
    public $showCreateModal = false;
    public $newGroup = [
        'name' => '',
        'description' => '',
        'members' => []
    ];
    public $users = [];

    public function mount()
    {
        // dd("asdfasd");
        $this->loadGroups();
        $this->users = User::where('id', '!=', Auth::id())->get();
    }

    public function loadGroups()
    {
        $this->groups = Auth::user()->groupChats()
    ->when($this->search, function ($query) {
        $query->where('name', 'like', '%' . $this->search . '%');
    })
    ->with(['creator', 'members.user']) // Load creator and each member's user info
    ->get();
    }
    public function createGroup()
    {
        $this->validate([
            'newGroup.name' => 'required|string|max:255',
            'newGroup.description' => 'nullable|string',
            'newGroup.members' => 'required|array|min:1',
        ]);

        // Create the group
        $group = GroupChat::create([
            'name' => $this->newGroup['name'],
            'description' => $this->newGroup['description'],
            'created_by' => Auth::id(),
        ]);

        // Add creator as admin
        GroupChatUser::create([
            'group_chat_id' => $group->id,
            'user_id' => Auth::id(),
            'is_admin' => true,
        ]);

        // Add other members (non-admins)
        foreach ($this->newGroup['members'] as $memberId) {
            // Avoid adding creator twice
            if ($memberId != Auth::id()) {
                GroupChatUser::create([
                    'group_chat_id' => $group->id,
                    'user_id' => $memberId,
                    'is_admin' => false,
                ]);
            }
        }

        // Reset form and refresh groups
        $this->reset('newGroup');
        $this->showCreateModal = false;
        $this->loadGroups();
    }

    public function render()
    {
        return view('livewire.group-chat-list');
    }
}
