<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\SendMessageRequest;
use App\Http\Requests\Chat\StartConversationRequest;
use App\Services\Chat\ChatService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ChatController extends Controller
{
    public function __construct(
        protected ChatService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $conversations = $this->service->getUserConversations(
            $request->user()->id
        );

        return response()->json([
            'data' => $conversations,
        ]);
    }
    public function store(StartConversationRequest $request): JsonResponse
    {

        $conversation = $this->service->getOrCreateConversation(
            $request->user()->id,
            $request->validated('user_id')
        );
        return response()->json([
            'success' => true,
            'data' => $conversation,
        ], 201);
    }
    public function messages(Request $request, int $conversation): JsonResponse
    {
        $messages = $this->service->getConversationMessages(
            $conversation,
            $request->user()->id,
            $request->integer('per_page', 20)
        );

        return response()->json([
            'success' => true,
            'data'    => $messages,
        ]);
    }
    public function sendMessage(SendMessageRequest $request, int $conversation): JsonResponse
    {
        $message = $this->service->sendMessage(
            $conversation,
            $request->user()->id,
            $request->validated('body')
        );

        return response()->json([
            'success' => true,
            'data'    => $message,
        ], 201);
    }
    public function markAsRead(Request $request, int $conversation): JsonResponse
    {
        $count = $this->service->markAsRead(
            $conversation,
            $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => "{$count} messages marked as read.",
        ]);
    }
}
