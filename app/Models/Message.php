<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable=[
        'sender_id',
        'reciever_id',
        'message',
        'file_name',
        'file_path',
        'file_original_name',
        'is_read',
    ];

    public function sender(){
        return $this->belongsTo(User::class, 'sender_id', 'id');
    }

    public function reciever(){
        return $this->belongsTo(User::class, 'reciever_id', 'id');
    }
}

