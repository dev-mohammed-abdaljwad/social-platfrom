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
    

    public $friendship;

    public function __construct($friendship)
    {
        
        $this->friendship = $friendship;
    }


public function broadcastOn()
{
    return [
       new PrivateChannel('friendships.' . $this->friendship->sender_id),
        new PrivateChannel('friendships.' . $this->friendship->receiver_id)
    ];
}
public function broadcastAs()
{
    return 'friend.request.accepted';
}
}
