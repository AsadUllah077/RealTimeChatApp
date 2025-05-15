<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'sender_id',
        'reciever_id',
        'group_id',
        'is_group',
        'message',
        'file_name',
        'file_path',
        'file_original_name',
        'file_type',
        'audio_path',
        'is_read'
    ];

    public function group()
    {
        return $this->belongsTo(GroupChat::class, 'group_id');
    }
    public function reactions()
    {
        return $this->hasMany(MessageReaction::class);
    }
    public function seenBy()
    {
        return $this->belongsToMany(User::class, 'message_seen', 'message_id', 'user_id')
            ->withTimestamps();
    }
    public function isSeenBy($userId)
    {
        return $this->seenBy()->where('user_id', $userId)->exists();
    }
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id', 'id');
    }

    public function reciever()
    {
        return $this->belongsTo(User::class, 'reciever_id', 'id');
    }
    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->created_at = Carbon::now();
        });
    }
//     public function seen_by()
// {
//     return $this->hasMany(SeenMessage::class); // or your actual model
// }



    public function unreadMessages()
    {
        return $this->hasMany(Message::class, 'sender_id', localKey: 'id')
            ->where('reciever_id', auth()->id())
            ->where('is_read', false);
    }
}
