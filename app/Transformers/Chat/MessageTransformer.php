<?php

namespace App\Transformers\Chat;

class MessageTransformer
{
    /**
     * Transform a single message for API response.
     *
     * @param \App\Models\Message $message
     * @return array
     */
    public static function transform($message): array
    {
        return [
            'id'              => $message->id,
            'conversation_id' => $message->conversation_id,
            'sender'          => [
                'id'     => $message->sender->id,
                'name'   => $message->sender->name,
                'avatar' => $message->sender->avatar_url,
            ],
            'body'       => $message->body,
            'read_at'    => $message->read_at?->toISOString(),
            'created_at' => $message->created_at->toISOString(),
        ];
    }

    /**
     * Transform a collection of messages.
     *
     * @param iterable $messages
     * @return array
     */
    public static function collection(iterable $messages): array
    {
        return collect($messages)->map(fn ($m) => self::transform($m))->toArray();
    }
}