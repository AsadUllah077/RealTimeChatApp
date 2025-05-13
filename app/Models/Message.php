<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'sender_id',
        'reciever_id',
        'message',
        'file_name',
        'file_path',
        'file_original_name',
        'is_read',
    ];

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

    
    public function unreadMessages()
    {
        return $this->hasMany(Message::class, 'sender_id', localKey: 'id')
            ->where('reciever_id', auth()->id())
            ->where('is_read', false);
    }
}
