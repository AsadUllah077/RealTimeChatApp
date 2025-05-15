<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function unreadMessages()
    {
        return $this->hasMany(Message::class, 'sender_id', 'id')->where('is_read', 0);
    }
    public function messages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }
    // public function groupChats()
    // {
    //     return $this->belongsToMany(GroupChatUser::class, 'group_chat_users', 'user_id', 'group_chat_id ');
    // }
    public function groupChats()
    {
        return $this->hasManyThrough(
            GroupChat::class,
            GroupChatUser::class,
            'user_id',        // Foreign key on group_chat_users table
            'id',             // Foreign key on group_chats table (primary key)
            'id',             // Local key on users table
            'group_chat_id'   // Local key on group_chat_users table
        );
    }
    public function groupChatUsers()
    {
        return $this->hasMany(GroupChatUser::class);
    }
}
