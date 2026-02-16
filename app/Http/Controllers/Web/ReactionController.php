<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Reaction\ReactionService;
use App\Services\Post\PostService;
use App\Services\Notification\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReactionController extends Controller
{
    public function __construct(
        protected ReactionService $reactionService,
        protected PostService $postService,
        protected NotificationService $notificationService
    ) {}

    public function reactToPost(Request $request, int $postId): JsonResponse
    {
        $request->validate([
            'type' => 'required|string|in:like,love,haha,wow,sad,angry',
        ]);

        $post = $this->postService->find($postId);
        if (!$post) {
            return response()->json(['success' => false, 'message' => 'Post not found'], 404);
        }

        $result = $this->reactionService->reactToPost(auth()->user(), $postId, $request->type);

        // Send notification if added or updated (and not self)
        if (in_array($result['action'], ['added', 'updated']) && $post->user_id !== auth()->id()) {
            // Note: We might want to add a specific reaction notification type later
            // For now, we can reuse the like notification or create a generic one
            $this->notificationService->create(
                $post->user_id,
                auth()->id(),
                \App\Models\Notification::TYPE_REACTION,
                $post,
                auth()->user()->name . " reacted to your post with " . $request->type,
                ['post_id' => $post->id, 'reaction_type' => $request->type]
            );
        }

        return response()->json([
            'success' => true,
            'action' => $result['action'],
            'counts' => $result['counts'],
            'user_reaction' => $result['reaction'] ? $result['reaction']->type->value : null,
        ]);
    }
}