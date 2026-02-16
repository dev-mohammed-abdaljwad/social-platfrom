<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

use App\Models\Notification;
use App\Services\Reaction\ReactionService;
use App\Services\Post\PostService;
use App\Services\Notification\NotificationService;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReactionController extends Controller
{
    public function __construct(
        protected ReactionService $reactionService,
        protected PostService $postService,
        protected NotificationService $notificationService
    ) {}

    public function reactToPost(Request $request, int $postId): JsonResponse
    {
        $validatedData = $request->validate([
            'type' => 'required|in:like,love,angry,sad,haha'
        ]);

        $post = $this->postService->find($postId);
        if (!$post) {
            return response()->json(['success' => false, 'message' => 'Post not found'], 404);
        }
        $result = $this->reactionService->reactToPost(auth()->user(), $postId, $validatedData['type']);
        // Send notification if added or updated (and not self)
        if (in_array($result['action'], ['added', 'updated']) && $post->user_id !== auth()->id()) {
            // Note: We might want to add a specific reaction notification type later
            // For now, we can reuse the like notification or create a generic one
            $this->notificationService->create(
                $post->user_id,
                auth()->id(),
                Notification::TYPE_REACTION,
                $post,
                auth()->user()->name . " reacted to your post with " . $validatedData['type'],
                ['post_id' => $post->id, 'reaction_type' => $validatedData['type']]
            );
        }

        return response()->json([
            'success' => true,
            'action' => $result['action'],
            'counts' => $result['counts'],
            'user_reaction' => $result['reaction'] ? $result['reaction']->type->value : null,
        ]);
    }


    public function reactToComment(Request $request, int $commentId): JsonResponse
    {
        $validatedData = $request->validate([
            'type' => 'required|in:like,love,angry,sad,haha'
        ]   );

        $result = $this->reactionService->reactToComment(auth()->user(), $commentId, $validatedData['type']);
        // send notification if added or updated (and not self)
        if (in_array($result['action'], ['added', 'updated']) && $result['reaction'] && $result['reaction']->reactable->user_id !== auth()->id()) {
            $comment = $result['reaction']->reactable;
            $this->notificationService->create(
                $comment->user_id,
                auth()->id(),
                Notification::TYPE_REACTION,
                $comment->post, // Notify on the post level for comment reactions
                auth()->user()->name . " reacted to your comment with " . $validatedData['type'],
                ['comment_id' => $comment->id, 'reaction_type' => $validatedData['type']]
            );
        }
        return response()->json([
            'success' => !isset($result['error']),
            'message' => $result['error'] ?? 'Reaction processed',
            'action' => $result['action'] ?? null,
            'counts' => $result['counts'] ?? null,
            'user_reaction' => $result['reaction'] ? $result['reaction']->type->value : null,
        ]); 
    }  
}