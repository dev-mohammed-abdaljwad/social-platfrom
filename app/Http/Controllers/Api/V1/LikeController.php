<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Like\LikeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Likes
 *
 * APIs for liking posts and comments
 */
class LikeController extends Controller
{
    public function __construct(
        protected LikeService $likeService
    ) {}

    /**
     * Toggle post like
     *
     * Like or unlike a post. If already liked, it will unlike.
     *
     * @urlParam postId integer required The post ID. Example: 1
     *
     * @response {
     *   "message": "Post liked",
     *   "action": "liked"
     * }
     * @response {
     *   "message": "Post unliked",
     *   "action": "unliked"
     * }
     */
    public function togglePostLike(Request $request, int $postId): JsonResponse
    {
        $result = $this->likeService->togglePostLike($request->user(), $postId);

        return response()->json([
            'message' => $result['action'] === 'liked' ? 'Post liked' : 'Post unliked',
            'action' => $result['action'],
        ]);
    }

    /**
     * Toggle comment like
     *
     * Like or unlike a comment. If already liked, it will unlike.
     *
     * @urlParam commentId integer required The comment ID. Example: 1
     *
     * @response {
     *   "message": "Comment liked",
     *   "action": "liked"
     * }
     * @response {
     *   "message": "Comment unliked",
     *   "action": "unliked"
     * }
     */
    public function toggleCommentLike(Request $request, int $commentId): JsonResponse
    {
        $result = $this->likeService->toggleCommentLike($request->user(), $commentId);

        return response()->json([
            'message' => $result['action'] === 'liked' ? 'Comment liked' : 'Comment unliked',
            'action' => $result['action'],
        ]);
    }

    /**
     * Check if post is liked
     *
     * Check if the authenticated user has liked a post.
     *
     * @urlParam postId integer required The post ID. Example: 1
     *
     * @response {
     *   "liked": true
     * }
     */
    public function hasLikedPost(Request $request, int $postId): JsonResponse
    {
        $hasLiked = $this->likeService->hasLikedPost($request->user(), $postId);

        return response()->json([
            'liked' => $hasLiked,
        ]);
    }

    /**
     * Check if comment is liked
     *
     * Check if the authenticated user has liked a comment.
     *
     * @urlParam commentId integer required The comment ID. Example: 1
     *
     * @response {
     *   "liked": false
     * }
     */
    public function hasLikedComment(Request $request, int $commentId): JsonResponse
    {
        $hasLiked = $this->likeService->hasLikedComment($request->user(), $commentId);

        return response()->json([
            'liked' => $hasLiked,
        ]);
    }
}
