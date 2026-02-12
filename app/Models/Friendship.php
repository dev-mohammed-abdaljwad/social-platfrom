<?php

namespace App\Models;

use App\Enums\FriendshipStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Friendship extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => FriendshipStatusEnum::class,
        ];
    }

    /**
     * Get the user who sent the friend request.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the user who received the friend request.
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Scope for pending friend requests.
     */
    public function scopePending($query)
    {
        return $query->where('status', FriendshipStatusEnum::Pending);
    }

    /**
     * Scope for accepted friendships.
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', FriendshipStatusEnum::Accepted);
    }

    /**
     * Check if friendship is accepted.
     */
    public function isAccepted(): bool
    {
        return $this->status === FriendshipStatusEnum::Accepted;
    }

    /**
     * Check if friendship is pending.
     */
    public function isPending(): bool
    {
        return $this->status === FriendshipStatusEnum::Pending;
    }
}
