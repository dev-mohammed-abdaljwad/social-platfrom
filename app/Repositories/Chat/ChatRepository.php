<?php

namespace App\Repositories\Chat;

use App\Models\Conversations;
use App\Models\Message;
use Illuminate\Database\Eloquent\Collection;

use Illuminate\Pagination\LengthAwarePaginator;

interface ChatRepository
{
    //
    public function findConversation(int $conversationId): Conversations;
    public function findConversationBetween (int $userOneId, int $userTwoId): ?Conversations; 
    public function createConversation(int $userOneId, int $userTwoId): Conversations;
    public function getConversationsForUser(int $userId): Collection;
    public function createMessage(int $conversationId, int $senderId, string $body):Message;
    public function getMessages(int $conversationId, int $perPage = 20): LengthAwarePaginator;
    public function markMessagesAsRead(int $conversationId, int $userId): int;
    public function getUnreadCountForUser(int $userId): int;
    public function getUnreadCountForConversation(int $conversationId, int $userId): int;
    public function loadParticipants(Conversations $conversation): Conversations;






}   
