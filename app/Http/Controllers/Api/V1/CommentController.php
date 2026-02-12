<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Comment\CreateCommentRequest;
use App\Http\Requests\Api\V1\Comment\UpdateCommentRequest;
use App\Services\Comment\CommentService;
use App\Transformers\Comment\CommentTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Comments
 *
 * APIs for managing comments on posts
 */
class CommentController extends Controller
{
    public function __construct(
        protected CommentService $commentService
    ) {}

    /**
     * List post comments
     *
     * Get all root-level comments for a post.
     *
     * @urlParam postId integer required The post ID. Example: 1
     *
     * @response {
     *   "data": [
     *     {
     *       "id": 1,
     *       "content": "Great post!",
     *       "user": {"id": 1, "name": "John Doe"},
     *       "likes_count": 3,
     *       "replies_count": 2,
     *       "created_at": "2026-02-12T10:00:00.000000Z"
     *     }
     *   ]
     * }
     */
    public function index(int $postId): JsonResponse
    {
        $comments = $this->commentService->findRootCommentsByPost($postId);

        return response()->json([
            'data' => CommentTransformer::collection($comments),
        ]);
    }

    /**
     * Create a comment
     *
     * Add a comment to a post. Can also create replies by providing parent_id.
     *
     * @urlParam postId integer required The post ID. Example: 1
     *
     * @bodyParam content string required The comment content. Example: Great post!
     * @bodyParam parent_id integer The parent comment ID for replies. Example: 1
     *
     * @response 201 {
     *   "message": "Comment created successfully",
     *   "data": {
     *     "id": 1,
     *     "content": "Great post!",
     *     "user": {"id": 1, "name": "John Doe"},
     *     "created_at": "2026-02-12T10:00:00.000000Z"
     *   }
     * }
     */
    public function store(CreateCommentRequest $request, int $postId): JsonResponse
    {
        $comment = $this->commentService->createForPost(
            $postId,
            $request->user()->id,
            $request->validated('content'),
            $request->validated('parent_id')
        );

        return response()->json([
            'message' => 'Comment created successfully',
            'data' => new CommentTransformer($comment->load('user')),
        ], 201);
    }

    /**
     * Get a comment
     *
     * Get a specific comment by ID.
     *
     * @urlParam postId integer required The post ID. Example: 1
     * @urlParam commentId integer required The comment ID. Example: 1
     *
     * @response {
     *   "data": {
     *     "id": 1,
     *     "content": "Great post!",
     *     "user": {"id": 1, "name": "John Doe"},
     *     "likes_count": 3,
     *     "created_at": "2026-02-12T10:00:00.000000Z"
     *   }
     * }
     */
    public function show(int $postId, int $commentId): JsonResponse
    {
        $comment = $this->commentService->find($commentId);

        return response()->json([
            'data' => new CommentTransformer($comment),
        ]);
    }

    /**
     * Update a comment
     *
     * Update a comment (must be the owner).
     *
     * @urlParam postId integer required The post ID. Example: 1
     * @urlParam commentId integer required The comment ID. Example: 1
     *
     * @bodyParam content string required The updated content. Example: Updated comment!
     *
     * @response {
     *   "message": "Comment updated successfully",
     *   "data": {
     *     "id": 1,
     *     "content": "Updated comment!"
     *   }
     * }
     * @response 403 {
     *   "message": "Unauthorized to update this comment"
     * }
     */
    public function update(UpdateCommentRequest $request, int $postId, int $commentId): JsonResponse
    {
        $comment = $this->commentService->find($commentId);

        // Check ownership
        if ($comment->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized to update this comment',
            ], 403);
        }

        $updated = $this->commentService->update($comment, $request->validated());

        return response()->json([
            'message' => 'Comment updated successfully',
            'data' => new CommentTransformer($updated),
        ]);
    }

    /**
     * Delete a comment
     *
     * Delete a comment (must be the owner).
     *
     * @urlParam postId integer required The post ID. Example: 1
     * @urlParam commentId integer required The comment ID. Example: 1
     *
     * @response {
     *   "message": "Comment deleted successfully"
     * }
     * @response 403 {
     *   "message": "Unauthorized to delete this comment"
     * }
     */
    public function destroy(Request $request, int $postId, int $commentId): JsonResponse
    {
        $comment = $this->commentService->find($commentId);

        // Check ownership
        if ($comment->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized to delete this comment',
            ], 403);
        }

        $this->commentService->delete($comment);

        return response()->json([
            'message' => 'Comment deleted successfully',
        ]);
    }

    /**
     * Get comment replies
     *
     * Get all replies to a specific comment.
     *
     * @urlParam postId integer required The post ID. Example: 1
     * @urlParam commentId integer required The comment ID. Example: 1
     *
     * @response {
     *   "data": [
     *     {
     *       "id": 2,
     *       "content": "I agree!",
     *       "user": {"id": 2, "name": "Jane Doe"},
     *       "created_at": "2026-02-12T11:00:00.000000Z"
     *     }
     *   ]
     * }
     */
    public function replies(int $postId, int $commentId): JsonResponse
    {
        $replies = $this->commentService->findReplies($commentId);

        return response()->json([
            'data' => CommentTransformer::collection($replies),
        ]);
    }
}
