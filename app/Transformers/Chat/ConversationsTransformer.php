<?php

namespace App\Transformers\Chat;

class ConversationTransformer
{
    /**
     * Transform a conversation for the inbox/list view.
     *
     * @param \App\Models\Conversation $conversation
     * @param int $currentUserId  To determine who the "other" user is
     * @return array
     */
    public static function transform($conversation, int $currentUserId): array
    {
        $otherUser = $conversation->user_one_id === $currentUserId
            ? $conversation->userTwo
            : $conversation->userOne;

        return [
            'id'           => $conversation->id,
            'other_user'   => [
                'id'     => $otherUser->id,
                'name'   => $otherUser->name,
                'avatar' => $otherUser->avatar_url,
            ],
            'last_message' => $conversation->latestMessage
                ? MessageTransformer::transform($conversation->latestMessage)
                : null,
            'unread_count' => $conversation->unread_count ?? 0,
            'updated_at'   => $conversation->updated_at->toISOString(),
        ];
    }

    /**
     * Transform a collection of conversations.
     *
     * @param iterable $conversations
     * @param int $currentUserId
     * @return array
     */
    public static function collection(iterable $conversations, int $currentUserId): array
    {
        return collect($conversations)
            ->map(fn ($c) => self::transform($c, $currentUserId))
            ->toArray();
    }
}