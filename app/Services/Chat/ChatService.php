<?php

namespace App\Services\Chat;

use App\Events\MessageSent;
use App\Models\Conversations;
use App\Models\Message;
use App\Repositories\Chat\ChatRepository;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ChatService
{
    public function __construct(
        protected ChatRepository $repository
    ) {
        //
    }

    // ──────────────────────────────────────────────
    //  1. GET OR CREATE CONVERSATION
    // ──────────────────────────────────────────────

    /**
     * Get an existing conversation between two users, or create one.
     *
     * Enforces min/max ordering to prevent duplicate conversations.
     * Example: getOrCreateConversation(7, 3) → stores as (3, 7)
     *
     * @param int $userA
     * @param int $userB
     * @return Conversations
     *
     * @throws \InvalidArgumentException if userA === userB
     */
    public function getOrCreateConversation(int $userA, int $userB): Conversations
    {
        // Prevent self-conversation
        if ($userA === $userB) {
            throw new \InvalidArgumentException('Cannot create a conversation with yourself.');
        }

        // Enforce min/max ordering to prevent duplicates
        $minUserId = min($userA, $userB);
        $maxUserId = max($userA, $userB);

        // Try to find existing conversation first
        $conversation = $this->repository->findConversationBetween($minUserId, $maxUserId);

        if ($conversation) {
            return $this->repository->loadParticipants($conversation);
        }

        // Create new conversation inside a transaction for safety
        $conversation = DB::transaction(function () use ($minUserId, $maxUserId) {
            return $this->repository->createConversation($minUserId, $maxUserId);
        });

        return $this->repository->loadParticipants($conversation);
    }

    // ──────────────────────────────────────────────
    //  2. SEND MESSAGE
    // ──────────────────────────────────────────────

    /**
     * Send a message in a conversation.
     *
     * 1. Validates that the sender belongs to the conversation
     * 2. Creates the message via repository (within DB transaction)
     * 3. Loads sender relationship (for broadcast payload)
     * 4. Fires the MessageSent event for real-time broadcasting
     * 5. Updates conversation's last_message_at timestamp
     *
     * @param int    $conversationId
     * @param int    $senderId
     * @param string $body
     * @return Message
     *
     * @throws AuthorizationException if sender is not a participant
     */
    public function sendMessage(int $conversationId, int $senderId, string $body): Message
    {
        // Load conversation and validate participation
        $conversation = $this->repository->findConversation($conversationId);
        $this->ensureParticipant($conversation, $senderId);

        // Create message and update conversation timestamp inside a transaction
        $message = DB::transaction(function () use ($conversation, $conversationId, $senderId, $body) {
            $message = $this->repository->createMessage($conversationId, $senderId, $body);

            // Update last_message_at for inbox ordering
            $conversation->update(['last_message_at' => now()]);

            return $message;
        });

        // Load sender relationship for the broadcast payload
        $message->load('sender:id,name,profile_picture');

        // Dispatch real-time event via Pusher
        broadcast(new MessageSent($message))->toOthers();

        return $message;
    }

    // ──────────────────────────────────────────────
    //  3. GET CONVERSATION MESSAGES (PAGINATED)
    // ──────────────────────────────────────────────

    /**
     * Get paginated messages for a conversation.
     *
     * Security: validates that the requesting user belongs to the conversation
     * before returning any messages.
     *
     * @param int $conversationId
     * @param int $userId         The user requesting messages (for authorization)
     * @param int $perPage
     * @return LengthAwarePaginator
     *
     * @throws AuthorizationException if user is not a participant
     */
    public function getConversationMessages(int $conversationId, int $userId, int $perPage = 20): LengthAwarePaginator
    {
        // Load conversation and validate participation
        $conversation = $this->repository->findConversation($conversationId);
        $this->ensureParticipant($conversation, $userId);

        return $this->repository->getMessages($conversationId, $perPage);
    }

    // ──────────────────────────────────────────────
    //  4. MARK AS READ
    // ──────────────────────────────────────────────

    /**
     * Mark all unread messages in a conversation as read for the given user.
     *
     * Only marks messages sent by the OTHER user — you don't "read" your own messages.
     *
     * @param int $conversationId
     * @param int $userId
     * @return int Number of messages marked as read
     *
     * @throws AuthorizationException if user is not a participant
     */
    public function markAsRead(int $conversationId, int $userId): int
    {
        // Load conversation and validate participation
        $conversation = $this->repository->findConversation($conversationId);
        $this->ensureParticipant($conversation, $userId);

        return $this->repository->markMessagesAsRead($conversationId, $userId);
    }

    // ──────────────────────────────────────────────
    //  5. GET USER CONVERSATIONS (INBOX)
    // ──────────────────────────────────────────────

    /**
     * Get all conversations for a user (inbox view).
     *
     * Returns conversations ordered by latest message, with:
     * - The other user's info (name, avatar)
     * - The last message preview
     * - Unread count for this user
     *
     * @param int $userId
     * @return Collection<int, Conversations>
     */
    public function getUserConversations(int $userId): Collection
    {
        return $this->repository->getConversationsForUser($userId);
    }

    // ──────────────────────────────────────────────
    //  6. GET UNREAD COUNT
    // ──────────────────────────────────────────────

    /**
     * Get the total unread message count for a user across all conversations.
     *
     * Useful for showing a badge count on chat icon.
     *
     * @param int $userId
     * @return int
     */
    public function getUnreadCount(int $userId): int
    {
        return $this->repository->getUnreadCountForUser($userId);
    }

    // ──────────────────────────────────────────────
    //  PRIVATE: SECURITY VALIDATION
    // ──────────────────────────────────────────────

    /**
     * Ensure a user is a participant of a conversation.
     *
     * This is the central security gate — called by every method
     * that accesses conversation data. If the user is not a
     * participant, an AuthorizationException is thrown.
     *
     * @param Conversations $conversation
     * @param int           $userId
     * @return void
     *
     * @throws AuthorizationException
     */
    private function ensureParticipant(Conversations $conversation, int $userId): void
    {
        if (!$conversation->hasParticipant($userId)) {
            throw new AuthorizationException('You are not a participant of this conversation.');
        }
    }
}
