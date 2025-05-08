<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;

class Chat extends Component
{
    public $userId, $user;
    public function mount($userId){
        $this->userId = $userId;
        $this->user = User::find($userId);
    }
    public function render()
    {
        
        return view('livewire.chat');
    }
}
