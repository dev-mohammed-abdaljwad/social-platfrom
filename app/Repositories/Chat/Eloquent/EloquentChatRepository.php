<?php

namespace App\Repositories\Chat\Eloquent;

use App\Models\Conversations;
use App\Models\Message;
use App\Repositories\Chat\ChatRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentChatRepository implements ChatRepository
{
    public function __construct(
        protected Message $messageModel,
        protected Conversations $conversationModel,
    ) {
        //
    }

    public function findConversation(int $conversationId): Conversations
    {
        return $this->conversationModel->with(['userOne', 'userTwo'])->findOrFail($conversationId);
    }
    public function findConversationBetween(int $userOneId, int $userTwoId): ?Conversations
    {
        return $this->conversationModel
            ->where(function ($query) use ($userOneId, $userTwoId) {
                $query->where('user_one_id', $userOneId)
                    ->where('user_two_id', $userTwoId);
            })
            ->orWhere(function ($query) use ($userOneId, $userTwoId) {
                $query->where('user_one_id', $userTwoId)
                    ->where('user_two_id', $userOneId);
            })
            ->first();
    }
    public function createConversation(int $userOneId, int $userTwoId): Conversations
    {
        return $this->conversationModel->create([
            'user_one_id' => $userOneId,
            'user_two_id' => $userTwoId,
        ]);
    }

    public function getConversationsForUser(int $userId): Collection
    {

        return $this->conversationModel
            ->forUser($userId)
            ->with(['userOne', 'userTwo', 'latestMessage'])
            ->get();
    }
    public function createMessage(int $conversationId, int $senderId, string $body): Message
    {
        return $this->messageModel->create([
            'conversation_id' => $conversationId,
            'sender_id' => $senderId,
            'body' => $body,
        ]);
    }
    /**
     * getMessages
     */

    public function getMessages(int $conversationId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->messageModel
            ->where('conversation_id', $conversationId)
            ->with(['sender:id,name,profile_picture'])  // profile_picture needed for avatar_url accessor
            ->orderBy('created_at', 'desc')             // newest-first; blade/JS reverses for display
            ->paginate($perPage);
    }
    // use index on conversation_id and sender_id for performance

    public function markMessagesAsRead(int $conversationId, int $userId): int
    {
        return $this->messageModel
            ->where('conversation_id', $conversationId)
            ->where('sender_id', '!=', $userId)   // only other user's messages
            ->whereNull('read_at')                 // only unread
            ->update(['read_at' => now()]);
    }
    public function getUnreadCountForUser(int $userId): int
    {

        return $this->messageModel
            ->whereNull('read_at')
            ->where('sender_id', '!=', $userId)
            ->whereHas('conversation', function ($query) use ($userId) {
                $query->where('user_one_id', $userId)
                    ->orWhere('user_two_id', $userId);
            })
            ->count();
    }
    public function getUnreadCountForConversation(int $conversationId, int $userId): int
    {
        return $this->messageModel
            ->where('conversation_id', $conversationId)
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->count();
    }
    public function loadParticipants(Conversations $conversation): Conversations
    {
        return $conversation->load(['userOne', 'userTwo']);
    }
}
