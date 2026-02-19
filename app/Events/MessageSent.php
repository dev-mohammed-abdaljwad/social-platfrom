<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Message $message)
    {
        //
    }

    /**
     * Broadcast on the private conversation channel.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->message->conversation_id),
        ];
    }

    /**
     * Custom event name so JS listens on 'message.sent'.
     */
    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    /**
     * Data payload sent to the frontend.
     * Includes both flat fields (for popup) and sender sub-object (for full-page chat).
     */
    public function broadcastWith(): array
    {
        $sender = $this->message->sender;

        return [
            'id'              => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'sender_id'       => $this->message->sender_id,
            'body'            => $this->message->body,
            'read_at'         => $this->message->read_at,
            'created_at'      => $this->message->created_at->toISOString(),
            // Sub-object so appendMessage() can do msg.sender?.avatar_url
            'sender' => [
                'id'         => $sender?->id,
                'name'       => $sender?->name,
                'avatar_url' => $sender?->avatar_url,
            ],
        ];
    }
}
