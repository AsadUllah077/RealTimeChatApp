<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Livewire\Chat;
use App\Livewire\GroupChat;
use App\Livewire\GroupChatList;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::controller(UserController::class)->group(function(){
    Route::get('dashboard', 'index')->name('dashboard')->middleware('auth');
    Route::get('dashboard/group-cht-ui', 'groupChatList')->name('group-cht-ui')->middleware('auth');
    Route::get('chat/{id}', 'userChat')->name('chat')->middleware('auth');
});
// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/chat/{userId}', Chat::class)->name('chat');
    Route::get('/group-chat/{groupId}', GroupChat::class)->name('group.chat');
    Route::get('/group-chats', GroupChatList::class)->name('group.chats');
});

require __DIR__.'/auth.php';
