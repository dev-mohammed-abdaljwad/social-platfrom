<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;


class FriendRequestAccepted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $senderId;
    public int $receiverId;
    public string $status;
    public ?array $fromUser;

    /**
     * @param int $senderId  The original friend-request sender's ID
     * @param int $receiverId The original friend-request receiver's ID
     * @param string $status  'pending' | 'friends' | 'none'
     * @param array|null $fromUser  The user who triggered the action (for toast)
     */
    public function __construct(int $senderId, int $receiverId, string $status, ?array $fromUser = null)
    {
        $this->senderId   = $senderId;
        $this->receiverId = $receiverId;
        $this->status     = $status;
        $this->fromUser   = $fromUser;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('friendships.' . $this->senderId),
            new PrivateChannel('friendships.' . $this->receiverId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'friendship.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'status'      => $this->status,
            'sender_id'   => $this->senderId,
            'receiver_id' => $this->receiverId,
            'from_user'   => $this->fromUser,
        ];
    }
}
