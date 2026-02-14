<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Comment\StoreCommentRequest;
use App\Services\Comment\CommentService;
use App\Services\Notification\NotificationService;
use App\Services\Post\PostService;

class CommentController extends Controller
{
    public function __construct(
        protected CommentService $commentService,
        protected PostService $postService,
        protected NotificationService $notificationService
    ) {}

    /**
     * Store a new comment.
     */
    public function store(StoreCommentRequest $request, int $postId)
    {
        $post = $this->postService->find($postId);
        $user = auth()->user();

        $comment = $this->commentService->createForPost(
            $post->id,
            $user->id,
            $request->validated('content'),
            $request->validated('parent_id')
        );

        // Send notification to post owner (if not self)
        if ($post->user_id !== $user->id) {
            $this->notificationService->postCommented(
                $post->user,
                $user,
                $post,
                $comment
            );
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'comment' => $this->commentService->formatNewComment($comment),
            ]);
        }

        return redirect()->back()->with('success', 'Comment added!');
    }

    /**
     * Delete a comment.
     */
    public function destroy(int $commentId)
    {
        $comment = $this->commentService->find($commentId);
        $user = auth()->user();

        // Authorization check via service
        if (!$this->commentService->canDelete($comment->user_id, $user->id)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $this->commentService->delete($comment);

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Comment deleted!');
    }

    /**
     * Get comments for a post (AJAX).
     */
    public function index(int $postId)
    {
        $user = auth()->user();
        $comments = $this->commentService->getCommentsForPost($postId, $user);

        return response()->json(['success' => true, 'comments' => $comments]);
    }

    /**
     * Toggle like on a comment.
     */
    public function toggleLike(int $commentId)
    {
        $user = auth()->user();
        $result = $this->commentService->toggleLike($user, $commentId);

        return response()->json([
            'success' => true,
            'liked' => $result['liked'],
            'likes_count' => $result['likes_count'],
        ]);
    }
}
