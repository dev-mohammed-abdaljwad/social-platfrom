<?php

namespace App\Http\Controllers\Web;

use App\Enums\ReactionTypeEnum;
use App\Http\Controllers\Controller;

use App\Models\Notification;
use App\Services\Reaction\ReactionService;
use App\Services\Post\PostService;
use App\Services\Notification\NotificationService;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
class ReactionController extends Controller
{
    public function __construct(
        protected ReactionService $reactionService,
        protected PostService $postService,
        protected NotificationService $notificationService
    ) {}

    public function reactToPost(Request $request, int $postId): JsonResponse
    {
        $data = $this->validateReaction($request);

        $post = $this->postService->find($postId);
        if (!$post) {
            return $this->error('Post not found', 404);
        }

        $result = $this->reactionService
            ->reactToPost($request->user(), $postId, $data['type']);

        $this->notifyPostOwner($result, $post, $data['type']);

        return $this->successResponse($result);
    }

    public function reactToComment(Request $request, int $commentId): JsonResponse
    {
        $data = $this->validateReaction($request);

        $result = $this->reactionService
            ->reactToComment($request->user(), $commentId, $data['type']);

        if (isset($result['error'])) {
            return $this->error($result['error'], 404);
        }

        $this->notifyCommentOwner($result, $data['type']);

        return $this->successResponse($result);
    }

    /* =========================
        Helpers
    ========================= */

    protected function validateReaction(Request $request): array
    {
        return $request->validate([
            'type' => ['required', Rule::in(ReactionTypeEnum::getvalues())]
        ]);
    }

    protected function notifyPostOwner(array $result, $post, string $type): void
    {
        if (
            !in_array($result['action'], ['added', 'updated']) ||
            $post->user_id === auth()->id()
        ) {
            return;
        }

        $this->notificationService->create(
            $post->user_id,
            auth()->id(),
            Notification::TYPE_REACTION,
            $post,
            auth()->user()->name . " reacted to your post",
            ['reaction_type' => $type]
        );
    }

    protected function notifyCommentOwner(array $result, string $type): void
    {
        if (
            !in_array($result['action'], ['added', 'updated']) ||
            !$result['reaction']
        ) {
            return;
        }

        $comment = $result['reaction']->reactable;

        if ($comment->user_id === auth()->id()) {
            return;
        }

        $this->notificationService->create(
            $comment->user_id,
            auth()->id(),
            Notification::TYPE_REACTION,
            $comment->post,
            auth()->user()->name . " reacted to your comment",
            ['comment_id' => $comment->id, 'reaction_type' => $type]
        );
    }

    protected function successResponse(array $result): JsonResponse
    {
        return response()->json([
            'success' => true,
            'action' => $result['action'] ?? null,
            'counts' => $result['counts'] ?? [],
            'user_reaction' => $result['reaction']?->type,
        ]);
    }

    protected function error(string $message, int $code = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message
        ], $code);
    }
}

