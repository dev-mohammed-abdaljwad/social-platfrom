<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
            'conversation_id',
        'sender_id',
        'body',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];

    }
    public function conversation()
    {
        return $this->belongsTo(Conversations::class);
    }
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');  
    }
    /**
     * Scope a query to only include unread messages for a given user.
     */
    public function scopeUnread($query, int $userId)
    {
        return $query->whereNull('read_at')
            ->where('sender_id', '!=', $userId);
    }
    

}
