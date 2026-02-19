# 🏗️ Real-Time 1-to-1 Chat System — Architecture Blueprint

> **Stack:** Laravel 12 · Private Pusher Channels · Raw Pusher JS (no Echo)
> **Pattern:** Controller → Service → Repository → Event (matching existing codebase)
> **Scope:** 1-to-1 private messaging only (no group chat)

---

## Table of Contents

1. [Database Design](#1-database-design)
2. [Models](#2-models)
3. [Repository Interface & Eloquent Implementation](#3-repository)
4. [ChatService (Full Production)](#4-chatservice)
5. [Broadcasting — MessageSent Event](#5-broadcasting)
6. [Channel Authorization](#6-channel-authorization)
7. [Form Requests](#7-form-requests)
8. [Controller](#8-controller)
9. [Transformer](#9-transformer)
10. [API Routes](#10-api-routes)
11. [Frontend — Raw Pusher JS](#11-frontend)
12. [Service Provider Binding](#12-service-provider)
13. [Performance Considerations](#13-performance)
14. [Security Checklist](#14-security)
15. [Clean Architecture Explanation](#15-architecture-explanation)

---

## 1. Database Design

### 1.1 `conversations` Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();

            // Always store the smaller user ID in user_one_id
            // This is the min/max ordering convention (explained below)
            $table->foreignId('user_one_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->foreignId('user_two_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->timestamps();

            // ─── Indexes ───
            // Unique constraint prevents duplicate conversations
            // With min/max ordering, (3,7) is ALWAYS stored as (3,7) never (7,3)
            $table->unique(['user_one_id', 'user_two_id'], 'unique_conversation');

            // Fast lookup: "give me all conversations for user X"
            $table->index('user_one_id');
            $table->index('user_two_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
```
conversationsAsUserOne
### 1.2 `messages` Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('conversation_id')
                  ->constrained('conversations')
                  ->cascadeOnDelete();

            $table->foreignId('sender_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->text('body');

            $table->timestamp('read_at')->nullable(); // null = unread
            $table->timestamps();

            // ─── Indexes ───
            // Most common query: messages for a conversation, ordered by time
            $table->index(['conversation_id', 'created_at']);

            // Unread count: WHERE conversation_id = ? AND sender_id != ? AND read_at IS NULL
            $table->index(['conversation_id', 'sender_id', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
```

### 1.3 Why min/max User Ordering?

```
Problem without ordering:
  User 3 starts chat with User 7  →  row: (user_one_id=3, user_two_id=7)
  User 7 starts chat with User 3  →  row: (user_one_id=7, user_two_id=3)  ← DUPLICATE!

The unique constraint (3,7) does NOT catch (7,3).

Solution — always store min(userA, userB) in user_one_id:
  User 3 starts chat with 7  →  min=3, max=7  →  (3, 7)  ✅
  User 7 starts chat with 3  →  min=3, max=7  →  (3, 7)  ✅ same row, unique catches it

This is enforced in ChatService::getOrCreateConversation().
```

---

## 2. Models

### 2.1 `Conversation` Model

**File:** `app/Models/Conversation.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_one_id',
        'user_two_id',
    ];

    // ─── Relationships ───

    /**
     * The first participant (always the lower user ID).
     */
    public function userOne(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_one_id');
    }

    /**
     * The second participant (always the higher user ID).
     */
    public function userTwo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_two_id');
    }

    /**
     * All messages in this conversation.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    // ─── Helper Methods ───

    /**
     * Check if a user is a participant of this conversation.
     *
     * @param int $userId
     * @return bool
     */
    public function hasParticipant(int $userId): bool
    {
        return $this->user_one_id === $userId || $this->user_two_id === $userId;
    }

    /**
     * Get the other participant's ID.
     *
     * @param int $userId  The current user's ID
     * @return int  The other user's ID
     */
    public function getOtherUserId(int $userId): int
    {
        return $this->user_one_id === $userId
            ? $this->user_two_id
            : $this->user_one_id;
    }

    // ─── Scopes ───

    /**
     * Scope: conversations that a specific user belongs to.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_one_id', $userId)
                     ->orWhere('user_two_id', $userId);
    }
}
```

### 2.2 `Message` Model

**File:** `app/Models/Message.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'body',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    // ─── Relationships ───

    /**
     * The conversation this message belongs to.
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * The user who sent this message.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    // ─── Scopes ───

    /**
     * Scope: unread messages (read_at IS NULL).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope: unread messages NOT sent by the given user.
     * (You only mark OTHER people's messages as "unread for you")
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnreadFor($query, int $userId)
    {
        return $query->whereNull('read_at')
                     ->where('sender_id', '!=', $userId);
    }
}
```

### 2.3 Add Relationships to Existing `User` Model

Add these methods to your existing `app/Models/User.php`:

```php
// ─── Chat Relationships ───

/**
 * Conversations where this user is participant one.
 */
public function conversationsAsUserOne(): HasMany
{
    return $this->hasMany(Conversation::class, 'user_one_id');
}

/**
 * Conversations where this user is participant two.
 */
public function conversationsAsUserTwo(): HasMany
{
    return $this->hasMany(Conversation::class, 'user_two_id');
}

/**
 * All messages sent by this user.
 */
public function sentMessages(): HasMany
{
    return $this->hasMany(Message::class, 'sender_id');
}
```

---

## 3. Repository

### 3.1 `ChatRepository` Interface

**File:** `app/Repositories/Chat/ChatRepository.php`

```php
<?php

namespace App\Repositories\Chat;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ChatRepository
{
    /**
     * Find a conversation by its ID.
     *
     * @param int $conversationId
     * @return Conversation
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findConversation(int $conversationId): Conversation;

    /**
     * Find existing conversation between two users (using min/max ordering).
     * Returns null if no conversation exists.
     *
     * @param int $userOneId  The smaller user ID (already ordered)
     * @param int $userTwoId  The larger user ID (already ordered)
     * @return Conversation|null
     */
    public function findConversationBetween(int $userOneId, int $userTwoId): ?Conversation;

    /**
     * Create a new conversation record.
     *
     * @param int $userOneId
     * @param int $userTwoId
     * @return Conversation
     */
    public function createConversation(int $userOneId, int $userTwoId): Conversation;

    /**
     * Get all conversations for a user, with the last message and other user eager-loaded.
     * Ordered by the most recent message (latest conversation first).
     *
     * @param int $userId
     * @return Collection<int, Conversation>
     */
    public function getConversationsForUser(int $userId): Collection;

    /**
     * Create a new message in a conversation.
     *
     * @param int $conversationId
     * @param int $senderId
     * @param string $body
     * @return Message
     */
    public function createMessage(int $conversationId, int $senderId, string $body): Message;

    /**
     * Get paginated messages for a conversation (newest first).
     *
     * @param int $conversationId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getMessages(int $conversationId, int $perPage = 20): LengthAwarePaginator;

    /**
     * Mark all unread messages in a conversation as read for a specific user.
     * Only marks messages NOT sent by this user (you don't read your own messages).
     *
     * @param int $conversationId
     * @param int $userId
     * @return int  Number of messages marked as read
     */
    public function markMessagesAsRead(int $conversationId, int $userId): int;

    /**
     * Get unread message count for a user across all conversations.
     *
     * @param int $userId
     * @return int
     */
    public function getUnreadCountForUser(int $userId): int;

    /**
     * Get unread message count for a specific conversation for a user.
     *
     * @param int $conversationId
     * @param int $userId
     * @return int
     */
    public function getUnreadCountForConversation(int $conversationId, int $userId): int;
}
```

### 3.2 `EloquentChatRepository` Implementation

**File:** `app/Repositories/Chat/Eloquent/EloquentChatRepository.php`

```php
<?php

namespace App\Repositories\Chat\Eloquent;

use App\Models\Conversation;
use App\Models\Message;
use App\Repositories\Chat\ChatRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class EloquentChatRepository implements ChatRepository
{
    public function __construct(
        protected Conversation $conversationModel,
        protected Message $messageModel
    ) {}
    
    public function findConversation(int $conversationId): Conversation
    {
        return $this->conversationModel
            ->with(['userOne', 'userTwo'])
            ->findOrFail($conversationId);
    }

    public function findConversationBetween(int $userOneId, int $userTwoId): ?Conversation
    {
        // userOneId and userTwoId are already min/max ordered by the service
        return $this->conversationModel
            ->where('user_one_id', $userOneId)
            ->where('user_two_id', $userTwoId)
            ->first();
    }

    public function createConversation(int $userOneId, int $userTwoId): Conversation
    {
        return $this->conversationModel->create([
            'user_one_id' => $userOneId,
            'user_two_id' => $userTwoId,
        ]);
    }

    public function getConversationsForUser(int $userId): Collection
    {
        // TODO: Implement — load conversations with latest message + other user
        // Use subquery ordering by latest message created_at
        // Eager-load: userOne, userTwo, and latestMessage (hasOne with latest scope)
        // Prevent N+1 by eager-loading relationships
    }

    public function createMessage(int $conversationId, int $senderId, string $body): Message
    {
        return $this->messageModel->create([
            'conversation_id' => $conversationId,
            'sender_id'       => $senderId,
            'body'            => $body,
        ]);
    }

    public function getMessages(int $conversationId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->messageModel
            ->where('conversation_id', $conversationId)
            ->with('sender:id,name,profile_picture')  // prevent N+1
            ->latest()                                  // newest first
            ->paginate($perPage);
    }

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
        // TODO: Implement — count unread messages across ALL user's conversations
        // Join conversations where user is participant
        // WHERE sender_id != $userId AND read_at IS NULL
    }

    public function getUnreadCountForConversation(int $conversationId, int $userId): int
    {
        return $this->messageModel
            ->where('conversation_id', $conversationId)
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->count();
    }
}
```

---

## 4. ChatService

**File:** `app/Services/Chat/ChatService.php`

This is the **core business logic layer**. The controller calls ONLY this service — never the repository directly.

```php
<?php

namespace App\Services\Chat;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Repositories\Chat\ChatRepository;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ChatService
{
    public function __construct(
        protected ChatRepository $repository
    ) {}

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
     * @return Conversation
     *
     * @throws \InvalidArgumentException  if userA === userB
     */
    public function getOrCreateConversation(int $userA, int $userB): Conversation
    {
        // Prevent self-conversation
        // Enforce min/max ordering
        // Use findConversationBetween() first
        // If null, createConversation()
        // Return conversation with participants loaded
    }

    // ──────────────────────────────────────────────
    //  2. SEND MESSAGE
    // ──────────────────────────────────────────────

    /**
     * Send a message in a conversation.
     *
     * 1. Validates that the sender belongs to the conversation
     * 2. Creates the message via repository
     * 3. Loads sender relationship (for broadcast payload)
     * 4. Fires the MessageSent event for real-time broadcasting
     *
     * @param int    $conversationId
     * @param int    $senderId
     * @param string $body
     * @return Message
     *
     * @throws AuthorizationException  if sender is not a participant
     */
    public function sendMessage(int $conversationId, int $senderId, string $body): Message
    {
        // Load conversation
        // Check hasParticipant($senderId) → throw AuthorizationException if false
        // Create message via repository
        // Load sender relationship on created message
        // Dispatch MessageSent event (broadcast)
        // Return message
    }

    // ──────────────────────────────────────────────
    //  3. GET CONVERSATION MESSAGES (PAGINATED)
    // ──────────────────────────────────────────────

    /**
     * Get paginated messages for a conversation.
     *
     * Security: validates that the requesting user belongs to the conversation.
     *
     * @param int $conversationId
     * @param int $userId         The user requesting messages (for authorization)
     * @param int $perPage
     * @return LengthAwarePaginator
     *
     * @throws AuthorizationException  if user is not a participant
     */
    public function getConversationMessages(int $conversationId, int $userId, int $perPage = 20): LengthAwarePaginator
    {
        // Load conversation
        // Check hasParticipant($userId) → throw AuthorizationException if false
        // Return repository->getMessages($conversationId, $perPage)
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
     * @return int  Number of messages marked as read
     *
     * @throws AuthorizationException  if user is not a participant
     */
    public function markAsRead(int $conversationId, int $userId): int
    {
        // Load conversation
        // Check hasParticipant($userId) → throw AuthorizationException if false
        // Return repository->markMessagesAsRead($conversationId, $userId)
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
     * @return Collection<int, Conversation>
     */
    public function getUserConversations(int $userId): Collection
    {
        // Return repository->getConversationsForUser($userId)
    }

    // ──────────────────────────────────────────────
    //  6. SECURITY VALIDATION (PRIVATE HELPER)
    // ──────────────────────────────────────────────

    /**
     * Ensure a user is a participant of a conversation.
     *
     * @param Conversation $conversation
     * @param int          $userId
     * @return void
     *
     * @throws AuthorizationException
     */
    private function ensureParticipant(Conversation $conversation, int $userId): void
    {
        // if (!$conversation->hasParticipant($userId))
        //     throw new AuthorizationException('You are not a participant of this conversation.');
    }
}
```

### Method Summary Table

| Method | Input | Output | Security |
|---|---|---|---|
| `getOrCreateConversation` | `int $userA, int $userB` | `Conversation` | Prevents self-chat, enforces min/max |
| `sendMessage` | `int $conversationId, int $senderId, string $body` | `Message` | Validates sender is participant |
| `getConversationMessages` | `int $conversationId, int $userId, int $perPage` | `LengthAwarePaginator` | Validates user is participant |
| `markAsRead` | `int $conversationId, int $userId` | `int` (count) | Validates user is participant |
| `getUserConversations` | `int $userId` | `Collection` | User sees only their own |
| `ensureParticipant` | `Conversation, int $userId` | `void` | Throws `AuthorizationException` |

---

## 5. Broadcasting — MessageSent Event

**File:** `app/Events/MessageSent.php`

```php
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

    /**
     * @param Message $message  The message that was just created (with sender loaded)
     */
    public function __construct(
        public Message $message
    ) {}

    /**
     * Broadcast on the private conversation channel.
     *
     * Only participants authorized via channels.php can subscribe.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->message->conversation_id),
        ];
    }

    /**
     * Custom event name for the frontend to bind to.
     */
    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    /**
     * Control exactly what data is sent over the wire.
     *
     * IMPORTANT: Never send the full Eloquent model — only the fields
     * the frontend actually needs. This prevents:
     * - Leaking sensitive data (emails, tokens)
     * - Sending unnecessary payload (model metadata, hidden fields)
     * - Breaking when model structure changes
     */
    public function broadcastWith(): array
    {
        return [
            'id'              => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'sender_id'       => $this->message->sender_id,
            'sender_name'     => $this->message->sender->name,
            'sender_avatar'   => $this->message->sender->avatar_url,
            'body'            => $this->message->body,
            'created_at'      => $this->message->created_at->toISOString(),
        ];
    }
}
```

### Why `ShouldBroadcastNow` instead of `ShouldBroadcast`?

| | `ShouldBroadcast` | `ShouldBroadcastNow` |
|---|---|---|
| Execution | Queued (goes through Redis/database queue) | Synchronous (immediate) |
| Latency | Higher (queue processing delay) | Instant |
| When to use | High traffic / production at scale | Development or low-to-medium traffic |

> **Recommendation:** Start with `ShouldBroadcastNow` for instant delivery. Switch to `ShouldBroadcast` when you set up a queue worker (`php artisan queue:work`) and traffic grows.

---

## 6. Channel Authorization

**File:** `routes/channels.php` — Add this channel:

```php
use App\Models\Conversation;

/**
 * Private channel authorization for chat conversations.
 *
 * Only users who are participants (user_one_id or user_two_id)
 * can subscribe to this channel.
 *
 * This prevents:
 * - User 99 subscribing to chat.5 (a conversation they're not in)
 * - Reading messages of other users
 * - ID tampering in Pusher subscription
 */
Broadcast::channel('chat.{conversationId}', function ($user, $conversationId) {
    $conversation = Conversation::find($conversationId);

    if (!$conversation) {
        return false;
    }

    return $conversation->hasParticipant($user->id);
});
```

### Security Flow:

```
Frontend: pusher.subscribe('private-chat.5')
    ↓
Pusher sends POST to /broadcasting/auth with channel name + auth token
    ↓
Laravel validates the user via Sanctum/session
    ↓
Laravel runs the channel callback above
    ↓
If user is NOT participant → returns false → Pusher rejects subscription
If user IS participant    → returns true  → Pusher allows subscription
```

---

## 7. Form Requests

### 7.1 `SendMessageRequest`

**File:** `app/Http/Requests/Chat/SendMessageRequest.php`

```php
<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization is handled in ChatService
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'body.required' => 'Message body is required.',
            'body.max'      => 'Message cannot exceed 5000 characters.',
        ];
    }
}
```

### 7.2 `StartConversationRequest`

**File:** `app/Http/Requests/Chat/StartConversationRequest.php`

```php
<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;

class StartConversationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'Target user ID is required.',
            'user_id.exists'   => 'The specified user does not exist.',
        ];
    }
}
```

---

## 8. Controller

**File:** `app/Http/Controllers/Api/ChatController.php`

> **Principle:** The controller is **thin**. It handles HTTP concerns only (validation, auth user, response formatting). **All business logic lives in `ChatService`.**

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\SendMessageRequest;
use App\Http\Requests\Chat\StartConversationRequest;
use App\Services\Chat\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function __construct(
        protected ChatService $chatService
    ) {}

    // ──────────────────────────────────────────────
    //  GET /api/chat/conversations
    //  Get all user conversations (inbox)
    // ──────────────────────────────────────────────

    /**
     * List all conversations for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $conversations = $this->chatService->getUserConversations(
            $request->user()->id
        );

        return response()->json([
            'success' => true,
            'data'    => $conversations,
        ]);
    }

    // ──────────────────────────────────────────────
    //  POST /api/chat/conversations
    //  Start or get existing conversation
    // ──────────────────────────────────────────────

    /**
     * Start a conversation with another user (or return existing one).
     */
    public function store(StartConversationRequest $request): JsonResponse
    {
        $conversation = $this->chatService->getOrCreateConversation(
            $request->user()->id,
            $request->validated('user_id')
        );

        return response()->json([
            'success' => true,
            'data'    => $conversation,
        ], 201);
    }

    // ──────────────────────────────────────────────
    //  GET /api/chat/conversations/{conversation}/messages
    //  Get messages for a conversation (paginated)
    // ──────────────────────────────────────────────

    /**
     * Get paginated messages for a conversation.
     */
    public function messages(Request $request, int $conversation): JsonResponse
    {
        $messages = $this->chatService->getConversationMessages(
            $conversation,
            $request->user()->id,
            $request->integer('per_page', 20)
        );

        return response()->json([
            'success' => true,
            'data'    => $messages,
        ]);
    }

    // ──────────────────────────────────────────────
    //  POST /api/chat/conversations/{conversation}/messages
    //  Send a message
    // ──────────────────────────────────────────────

    /**
     * Send a message in a conversation.
     */
    public function sendMessage(SendMessageRequest $request, int $conversation): JsonResponse
    {
        $message = $this->chatService->sendMessage(
            $conversation,
            $request->user()->id,
            $request->validated('body')
        );

        return response()->json([
            'success' => true,
            'data'    => $message,
        ], 201);
    }

    // ──────────────────────────────────────────────
    //  POST /api/chat/conversations/{conversation}/read
    //  Mark messages as read
    // ──────────────────────────────────────────────

    /**
     * Mark all messages in a conversation as read for the authenticated user.
     */
    public function markAsRead(Request $request, int $conversation): JsonResponse
    {
        $count = $this->chatService->markAsRead(
            $conversation,
            $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => "{$count} messages marked as read.",
        ]);
    }
}
```

---

## 9. Transformer (Optional)

**File:** `app/Transformers/V1/Chat/MessageTransformer.php`

Following your existing transformer pattern:

```php
<?php

namespace App\Transformers\V1\Chat;

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
```

**File:** `app/Transformers/V1/Chat/ConversationTransformer.php`

```php
<?php

namespace App\Transformers\V1\Chat;

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
```

---

## 10. API Routes

**File:** `routes/api.php` — Add these routes:

```php
use App\Http\Controllers\Api\ChatController;

// ─── Chat Routes (authenticated) ───
Route::middleware('auth:sanctum')->prefix('chat')->group(function () {

    // GET    /api/chat/conversations                        → inbox
    Route::get('/conversations', [ChatController::class, 'index']);

    // POST   /api/chat/conversations                        → start/get conversation
    Route::post('/conversations', [ChatController::class, 'store']);

    // GET    /api/chat/conversations/{conversation}/messages → get messages (paginated)
    Route::get('/conversations/{conversation}/messages', [ChatController::class, 'messages']);

    // POST   /api/chat/conversations/{conversation}/messages → send message
    Route::post('/conversations/{conversation}/messages', [ChatController::class, 'sendMessage']);

    // POST   /api/chat/conversations/{conversation}/read     → mark as read
    Route::post('/conversations/{conversation}/read', [ChatController::class, 'markAsRead']);
});
```

---

## 11. Frontend — Raw Pusher JS

### 11.1 Pusher Setup & Subscription

```javascript
// ─── Global Constants (set from Blade/API) ───
const CURRENT_USER_ID = {{ auth()->id() }};   // or from your auth API
const PUSHER_KEY      = '{{ env("PUSHER_APP_KEY") }}';
const PUSHER_CLUSTER  = '{{ env("PUSHER_APP_CLUSTER") }}';

// ─── Initialize Pusher ───
const pusher = new Pusher(PUSHER_KEY, {
    cluster: PUSHER_CLUSTER,
    forceTLS: true,

    // Custom authorizer for private channels (no Echo needed)
    authorizer: function (channel, options) {
        return {
            authorize: function (socketId, callback) {
                fetch('/broadcasting/auth', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        // If using Sanctum API tokens:
                        // 'Authorization': 'Bearer ' + API_TOKEN,
                    },
                    body: JSON.stringify({
                        socket_id: socketId,
                        channel_name: channel.name,
                    }),
                })
                .then(response => response.json())
                .then(data => callback(null, data))
                .catch(error => callback(error));
            }
        };
    }
});

// ─── Track rendered message IDs to prevent duplicates ───
const renderedMessageIds = new Set();

/**
 * Subscribe to a conversation's private channel.
 *
 * @param {number} conversationId
 */
function subscribeToConversation(conversationId) {
    const channel = pusher.subscribe('private-chat.' + conversationId);

    channel.bind('message.sent', function (data) {
        // ── Prevent duplicate rendering ──
        // This can happen if:
        //   1. The sender receives their own broadcast
        //   2. Network reconnection replays events
        if (renderedMessageIds.has(data.id)) {
            return;
        }

        renderedMessageIds.add(data.id);
        appendMessage(data);
    });

    channel.bind('pusher:subscription_error', function (status) {
        console.error('Subscription error for chat.' + conversationId, status);
    });
}

/**
 * Unsubscribe from a conversation channel.
 *
 * @param {number} conversationId
 */
function unsubscribeFromConversation(conversationId) {
    pusher.unsubscribe('private-chat.' + conversationId);
}
```

### 11.2 Append Message to DOM

```javascript
/**
 * Append a new message to the chat window.
 *
 * @param {Object} data - The message payload from broadcastWith()
 * @param {number} data.id
 * @param {number} data.sender_id
 * @param {string} data.sender_name
 * @param {string} data.sender_avatar
 * @param {string} data.body
 * @param {string} data.created_at
 */
function appendMessage(data) {
    const chatContainer = document.getElementById('chat-messages');
    if (!chatContainer) return;

    const isMine = data.sender_id === CURRENT_USER_ID;

    const messageEl = document.createElement('div');
    messageEl.classList.add('message', isMine ? 'message--sent' : 'message--received');
    messageEl.dataset.messageId = data.id;  // for deduplication checks

    messageEl.innerHTML = `
        <img class="message__avatar" src="${data.sender_avatar}" alt="${data.sender_name}">
        <div class="message__content">
            <span class="message__sender">${data.sender_name}</span>
            <p class="message__body">${escapeHtml(data.body)}</p>
            <span class="message__time">${formatTime(data.created_at)}</span>
        </div>
    `;

    chatContainer.appendChild(messageEl);

    // Auto-scroll to bottom
    chatContainer.scrollTop = chatContainer.scrollHeight;
}

/**
 * Escape HTML to prevent XSS.
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Format ISO timestamp to human-readable time.
 */
function formatTime(isoString) {
    const date = new Date(isoString);
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}
```

### 11.3 Send Message via AJAX

```javascript
/**
 * Send a message to a conversation via API.
 *
 * @param {number} conversationId
 * @param {string} body
 */
async function sendMessage(conversationId, body) {
    try {
        const response = await fetch(`/api/chat/conversations/${conversationId}/messages`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                // 'Authorization': 'Bearer ' + API_TOKEN,  // if using Sanctum tokens
            },
            body: JSON.stringify({ body: body }),
        });

        const result = await response.json();

        if (result.success) {
            // Message will arrive via Pusher broadcast
            // But we can also immediately render it for instant UX:
            if (!renderedMessageIds.has(result.data.id)) {
                renderedMessageIds.add(result.data.id);
                appendMessage({
                    id:            result.data.id,
                    sender_id:     CURRENT_USER_ID,
                    sender_name:   'You', // or current user name
                    sender_avatar: CURRENT_USER_AVATAR,
                    body:          result.data.body,
                    created_at:    result.data.created_at,
                });
            }
        }
    } catch (error) {
        console.error('Failed to send message:', error);
    }
}

// ─── Form Handler ───
document.getElementById('chat-form')?.addEventListener('submit', function (e) {
    e.preventDefault();
    const input = document.getElementById('chat-input');
    const body = input.value.trim();

    if (!body) return;

    sendMessage(CURRENT_CONVERSATION_ID, body);
    input.value = '';
});
```

---

## 12. Service Provider Binding

**File:** `app/Providers/AppServiceProvider.php` — Add in `register()`:

```php
use App\Repositories\Chat\ChatRepository;
use App\Repositories\Chat\Eloquent\EloquentChatRepository;

public function register(): void
{
    // ... existing bindings ...

    $this->app->bind(
        ChatRepository::class,
        EloquentChatRepository::class
    );
}
```

---

## 13. Performance Considerations

### 13.1 Index Recommendations

| Index | Table | Purpose |
|---|---|---|
| `unique(user_one_id, user_two_id)` | `conversations` | Prevent duplicate conversations |
| `index(user_one_id)` | `conversations` | Fast lookup for user's conversations |
| `index(user_two_id)` | `conversations` | Fast lookup for user's conversations |
| `index(conversation_id, created_at)` | `messages` | Fast message retrieval (paginated, ordered) |
| `index(conversation_id, sender_id, read_at)` | `messages` | Fast unread count queries |

### 13.2 Pagination Strategy

```
❌ BAD:  Load all messages at once → memory explosion at scale
✅ GOOD: Use cursor-based or offset pagination with 20-50 messages per page

For chat specifically:
- Use standard pagination for API responses
- On frontend: load initial page, then "Load older messages" button
- Consider cursor pagination (lastId) for infinite scroll (same pattern as your feed)
```

### 13.3 N+1 Prevention

```php
// ❌ BAD — N+1 when accessing sender for each message
$messages = Message::where('conversation_id', $id)->get();
foreach ($messages as $msg) {
    echo $msg->sender->name;  // N additional queries!
}

// ✅ GOOD — Eager load with field selection
$messages = Message::where('conversation_id', $id)
    ->with('sender:id,name,profile_picture')
    ->latest()
    ->paginate(20);
```

### 13.4 When to Switch to Queue

| Condition | Use `ShouldBroadcastNow` | Use `ShouldBroadcast` (queued) |
|---|---|---|
| Users | < 1,000 concurrent | > 1,000 concurrent |
| Messages/sec | < 50 | > 50 |
| Server Load | Low | High — need to offload to workers |
| Latency tolerance | None (instant only) | 50-200ms acceptable |

**To switch:** Change `implements ShouldBroadcastNow` to `implements ShouldBroadcast` and run `php artisan queue:work`.

---

## 14. Security Checklist

### 14.1 Prevent Unauthorized Message Sending

```
✅ ChatService::sendMessage() calls ensureParticipant()
   → Verifies sender_id matches a participant of the conversation
   → Throws AuthorizationException if not

✅ Controller uses $request->user()->id (from Sanctum auth)
   → User cannot fake their sender_id
   → The ID comes from the authenticated session, NOT from the request body
```

### 14.2 Prevent Subscribing to Other Users' Conversations

```
✅ Channel authorization in routes/channels.php
   → Broadcast::channel('chat.{conversationId}', ...)
   → Checks $conversation->hasParticipant($user->id)
   → Pusher REJECTS subscription if false

✅ Flow:
   1. User tries to subscribe to private-chat.99
   2. Pusher calls /broadcasting/auth
   3. Laravel checks: is user a participant of conversation 99?
   4. If NO → 403 → subscription denied
```

### 14.3 Protect Against ID Tampering

```
✅ sender_id is NEVER taken from request body
   → Always: $request->user()->id (from auth middleware)
   → Even if user sends { "sender_id": 999 } in the body, it's ignored

✅ conversation_id comes from URL parameter
   → But authorization is checked: user must be a participant
   → So even if they guess conversation ID 500, they can't access it

✅ user_id in StartConversationRequest
   → Validated: 'exists:users,id'
   → Can only start conversation with real users
   → Min/max ordering prevents duplicates regardless of who initiates
```

---

## 15. Clean Architecture Explanation

### Layer Diagram

```
┌──────────────────────────────────────────────┐
│                 HTTP Request                  │
└──────────────────┬───────────────────────────┘
                   │
┌──────────────────▼───────────────────────────┐
│            Form Request (Validation)          │  ← Validates input
└──────────────────┬───────────────────────────┘
                   │
┌──────────────────▼───────────────────────────┐
│         Controller (Thin HTTP Layer)          │  ← Routes to service, formats response
│  • Gets auth user from $request->user()       │
│  • Calls ChatService methods                  │
│  • Returns JsonResponse                       │
└──────────────────┬───────────────────────────┘
                   │
┌──────────────────▼───────────────────────────┐
│          Service (Business Logic)             │  ← Core rules: authorization, min/max,
│  • getOrCreateConversation()                  │     event dispatching
│  • sendMessage()                              │
│  • ensureParticipant()                        │
│  • Dispatches Events                          │
└──────────────────┬───────────────────────────┘
                   │
┌──────────────────▼───────────────────────────┐
│         Repository Interface                  │  ← Abstracts data access
│  (ChatRepository)                             │
└──────────────────┬───────────────────────────┘
                   │
┌──────────────────▼───────────────────────────┐
│    Eloquent Implementation                    │  ← Concrete DB operations
│  (EloquentChatRepository)                     │
└──────────────────┬───────────────────────────┘
                   │
┌──────────────────▼───────────────────────────┐
│         Event / Broadcasting                  │  ← Real-time via Pusher
│  (MessageSent → ShouldBroadcastNow)           │
└──────────────────────────────────────────────┘
```

### Why This Structure is Production-Ready

| Principle | How It's Applied |
|---|---|
| **Single Responsibility** | Each layer has ONE job: Controller = HTTP, Service = business logic, Repository = data access |
| **Dependency Inversion** | Service depends on `ChatRepository` interface, not `EloquentChatRepository`. Swap to Redis/MongoDB by changing the binding. |
| **Open/Closed** | Add group chat later by extending, not modifying existing code. Add a `GroupChatService` that uses the same `ChatRepository` interface. |
| **Testability** | Mock `ChatRepository` in service unit tests. Mock `ChatService` in controller tests. No database needed for business logic tests. |
| **Security by Design** | Authorization at service level (not controller). Even if you add a CLI command or job that calls `sendMessage()`, the security check is always enforced. |
| **Scalability** | Repository abstraction allows caching layer insertion. Event-based broadcasting allows swapping to queued processing. Pagination prevents memory issues. |
| **Consistency** | Follows the exact same pattern as your existing `FriendshipService → FriendshipRepository → EloquentFriendshipRepository` structure. |

### File Structure Summary

```
app/
├── Events/
│   └── MessageSent.php                          ← ShouldBroadcastNow event
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       └── ChatController.php               ← Thin controller
│   └── Requests/
│       └── Chat/
│           ├── SendMessageRequest.php            ← Validation
│           └── StartConversationRequest.php      ← Validation
├── Models/
│   ├── Conversation.php                          ← Model + relationships + scopes
│   ├── Message.php                               ← Model + relationships + scopes
│   └── User.php                                  ← Add chat relationships
├── Repositories/
│   └── Chat/
│       ├── ChatRepository.php                    ← Interface
│       └── Eloquent/
│           └── EloquentChatRepository.php        ← Implementation
├── Services/
│   └── Chat/
│       └── ChatService.php                       ← Business logic
└── Transformers/
    └── V1/
        └── Chat/
            ├── ConversationTransformer.php        ← Response formatting
            └── MessageTransformer.php             ← Response formatting

database/
└── migrations/
    ├── xxxx_xx_xx_create_conversations_table.php
    └── xxxx_xx_xx_create_messages_table.php

routes/
├── api.php                                       ← Chat API routes
└── channels.php                                  ← + chat.{conversationId} auth
```

---

## Quick Implementation Checklist

- [ ] Create `conversations` migration
- [ ] Create `messages` migration
- [ ] Run `php artisan migrate`
- [ ] Create `Conversation` model
- [ ] Create `Message` model
- [ ] Add chat relationships to `User` model
- [ ] Create `ChatRepository` interface
- [ ] Create `EloquentChatRepository`
- [ ] Bind interface → implementation in `AppServiceProvider`
- [ ] Create `ChatService`
- [ ] Create `MessageSent` event
- [ ] Add `chat.{conversationId}` channel auth in `channels.php`
- [ ] Create `SendMessageRequest` form request
- [ ] Create `StartConversationRequest` form request
- [ ] Create `ChatController`
- [ ] Add routes to `api.php`
- [ ] Create transformers (optional)
- [ ] Add Pusher JS to frontend
- [ ] Test end-to-end
