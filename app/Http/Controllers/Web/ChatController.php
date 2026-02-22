<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Services\Chat\ChatService;
use App\Services\Notification\NotificationService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function __construct(
        protected ChatService $chatService,
        protected NotificationService $notificationService
    ) {
        //
    }

    /**
     * GET /chat — inbox page (no conversation selected).
     */
    public function index(Request $request)
    {
        $user          = auth()->user();
        $conversations = $this->chatService->getUserConversations($user->id);

        return view('chat.index', [
            'conversations' => $conversations,
            'currentUserId' => $user->id,
        ]);
    }

    /**
     * GET /chat/{conversation} — inbox page with active conversation.
     */
    public function show(Request $request, int $conversation)
    {
        $user          = auth()->user();
        $conversations = $this->chatService->getUserConversations($user->id);

        // Authorization: ensure the user belongs to this conversation
        $conv = $conversations->firstWhere('id', $conversation);
        if (! $conv) {
            return redirect()->route('chat.index')->with('error', 'Conversation not found.');
        }

        // Load messages (newest-first via paginator, reversed in blade)
        try {
            $messages = $this->chatService->getConversationMessages($conversation, $user->id, 30);
        } catch (AuthorizationException) {
            return redirect()->route('chat.index')->with('error', 'Unauthorized.');
        }

        // Mark as read silently
        try {

            $this->chatService->markAsRead($conversation, $user->id);
        } catch (AuthorizationException) {
            return redirect()->route('chat.index')->with('error', 'Unauthorized.');
        }
        return view('chat.index', [
            'conversations'      => $conversations,
            'currentUserId'      => $user->id,
            'activeConversation' => $conversation,
            'messages'           => $messages,
            'otherUser'          => $conv->user_one_id == $user->id ? $conv->userTwo : $conv->userOne,
        ]);
    }
    /**
     * POST /chat/start — AJAX: get-or-create conversation.
     */
    public function startConversation(Request $request): JsonResponse
    {
        $request->validate(['user_id' => 'required|integer|exists:users,id']);

        $user         = auth()->user();
        $conversation = $this->chatService->getOrCreateConversation($user->id, (int) $request->user_id);

        return response()->json([
            'success'      => true,
            'conversation' => $conversation,
        ]);
    }

    /**
     * GET /chat/{conversation}/messages — AJAX: paginated messages.
     */
    public function getMessages(Request $request, int $conversation): JsonResponse
    {
        $user = auth()->user();

        try {
            $paginator = $this->chatService->getConversationMessages(
                $conversation,
                $user->id,
                $request->integer('per_page', 30)
            );
        } catch (AuthorizationException) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $this->chatService->markAsRead($conversation, $user->id);

        return response()->json([
            'success'  => true,
            'messages' => $paginator->items(),
            'has_more' => $paginator->hasMorePages(),
        ]);
    }

    /**
     * POST /chat/{conversation}/send — AJAX: send a message
     * I Want to send notifciation for user useing  NotificationService
     */

    public function sendMessage(Request $request, int $conversation): JsonResponse
    {
        $request->validate(['body' => 'required|string|max:5000']);

        $user    = auth()->user();
        try {
            $message = $this->chatService->sendMessage(
                $conversation,
                $user->id,
                $request->string('body')
            );
        } catch (AuthorizationException) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }
        $this->notificationService->create(
            $message->conversation->user_one_id === $user->id ? $message->conversation->user_two_id : $message->conversation->user_one_id,
            $user->id,
            Notification::TYPE_MESSAGE,
            $message,
            auth()->user()->name . " sent you a message",
            ['conversation_id' => $conversation, 'message_id' => $message->id]
        );

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    /**
     * POST /chat/{conversation}/read — AJAX: mark conversation read.
     */
    public function markAsRead(Request $request, int $conversation): JsonResponse
    {
        $user  = auth()->user();
        try {
            $count = $this->chatService->markAsRead($conversation, $user->id);
        } catch (AuthorizationException) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'count'   => $count,
        ]);
    }

    /**
     * GET /chat/unread-count — AJAX: total unread messages for current user.
     */
    public function unreadCount(): JsonResponse
    {
        $user  = auth()->user();
        $count = $this->chatService->getUnreadCount($user->id);

        return response()->json([
            'success' => true,
            'count'   => $count,
        ]);
    }
}
