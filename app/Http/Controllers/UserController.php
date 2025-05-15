<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index(){
        $users = User::where('id', '!=', Auth::id())
    ->withCount([
        'messages as unread_messages_count' => function (Builder $query) {
            $query->where('reciever_id', Auth::id())
                  ->where('is_read', false);
        }
    ])
    ->get();
        // dd($users);
        return view('dashboard', compact('users'));
    }

    public function groupChatList(){
        return view('group-chats');
    }

    public function userChat($id){
        $userId = $id;
        return view('chat-page', compact('userId'));
    }
}
