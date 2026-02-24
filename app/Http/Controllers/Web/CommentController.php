<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Services\Comment\CommentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\Notification\NotificationService;

class CommentController extends Controller
{
    public function __construct(
        protected CommentService $commentService,
        protected NotificationService $notificationService
    ) {}

    /**
     * Get all comments for a post with reactions
     */
    public function getComments(Post $post): JsonResponse
    {
        $comments = $this->commentService->getCommentsWithReactions(
            $post->id,
            auth()->user()
        );


        return response()->json([
            'success' => true,
            'comments' => $comments,
        ]);
    }

    /**
     * Create a new comment
     */
    public function store(Request $request, Post $post): JsonResponse
    {
        $request->validate([
            'content' => 'required|string|max:500',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        $comment = $this->commentService->createForPost(
            $post->id,
            auth()->id(),
            $request->content,
            $request->parent_id
        );

        // Notifications
        $currentUser = auth()->user();

        if ($request->parent_id) {
            // It's a reply: notify the owner of the parent comment
            $parentComment = $this->commentService->find($request->parent_id);
            if ($parentComment && $parentComment->user_id !== $currentUser->id) {
                $this->notificationService->commentReplied(
                    $parentComment->user,
                    $currentUser,
                    $parentComment,
                    $comment
                );
            }
        } else {
            // It's a top-level comment: notify the post owner
            if ($post->user_id !== $currentUser->id) {
                $this->notificationService->postCommented(
                    $post->user,
                    $currentUser,
                    $post,
                    $comment
                );
            }
        }

        return response()->json([
            'success' => true,
            'comment' => $this->commentService->formatNewComment($comment, auth()->user()),
        ], 201);
    }

    /**
     * Delete a comment
     */
    public function destroy(int $commentId): JsonResponse
    {
        $comment = $this->commentService->find($commentId);

        if (!$comment) {
            return response()->json([
                'success' => false,
                'message' => 'Comment not found',
            ], 404);
        }

        if (!$this->commentService->canDelete($comment->user_id, auth()->id())) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $this->commentService->delete($comment);

        return response()->json([
            'success' => true,
            'message' => 'Comment deleted',
        ]);
    }
}
