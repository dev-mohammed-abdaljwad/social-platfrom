<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Services\Comment\CommentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CommentController extends Controller
{
    public function __construct(
        protected CommentService $commentService
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
        ]);

        $comment = $this->commentService->createForPost(
            $post->id,
            auth()->id(),
            $request->content
        );

        // Return formatted comment with reaction data
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