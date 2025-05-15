<?php

// app/Models/GroupChat.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupChat extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'avatar', 'created_by'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // public function members()
    // {
    //     return $this->belongsToMany(User::class, 'group_chat_users')
    //         ->withPivot('is_admin')
    //         ->withTimestamps();
    // }

    public function members()
{
    return $this->hasMany(GroupChatUser::class);
}
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function isMember($userId)
    {
        return $this->members()->where('user_id', $userId)->exists();
    }   

    public function isAdmin($userId)
    {
        return $this->members()->where('user_id', $userId)->where('is_admin', true)->exists();
    }

//     public function creator()
// {
//     return $this->belongsTo(User::class, 'creator_id'); // If applicable
// }

}
