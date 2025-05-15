<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupChatUser extends Model
{
    use HasFactory;

    protected $table = 'group_chat_users';

    protected $fillable = ['group_chat_id', 'user_id', 'is_admin'];

    public function group()
    {
        return $this->belongsTo(GroupChat::class, 'group_chat_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function members()
{
    return $this->hasMany(GroupChatUser::class);
}
}
