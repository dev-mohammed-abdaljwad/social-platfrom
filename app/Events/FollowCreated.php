<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FollowCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public $follower;
    public $followee;
    public function __construct($follower, $followee)
    {
        $this->follower = $follower;
        $this->followee = $followee;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('follow-created', $this->follower->id),
        ];
    }
    public function broadcastWith(): array
    {
        return [
            'follower' => $this->follower,
            'followee' => $this->followee,
        ];
    }
}
