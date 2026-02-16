<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Post;
use App\Services\Like\LikeService;
use App\Transformers\Like\LikeTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function __construct(
        protected LikeService $likeService
    ) {}

    public function getPostLikes(int $postId): JsonResponse
    {
        $likes = $this->likeService->findByLikeable(Post::class, $postId);

        return response()->json([
            'data' => LikeTransformer::collection($likes),
        ]);
    }

    public function togglePostLike(Request $request, int $postId): JsonResponse
    {
        $result = $this->likeService->togglePostLike($request->user(), $postId);

        $response = [
            'message' => $result['liked'] ? 'Post liked' : 'Post unliked',
            'liked' => $result['liked'],
            'likes_count' => $result['likes_count'],
        ];

        if ($result['liked'] && $result['like']) {
            $response['data'] = new LikeTransformer($result['like']->load('user'));
        }

        return response()->json($response);
    }

    public function toggleCommentLike(Request $request, int $commentId): JsonResponse
    {
        $result = $this->likeService->toggleCommentLike($request->user(), $commentId);

        $response = [
            'message' => $result['liked'] ? 'Comment liked' : 'Comment unliked',
            'liked' => $result['liked'],
            'likes_count' => $result['likes_count'],
        ];

        if ($result['liked'] && $result['like']) {
            $response['data'] = new LikeTransformer($result['like']->load('user'));
        }

        return response()->json($response);
    }

    public function hasLikedPost(Request $request, int $postId): JsonResponse
    {
        $hasLiked = $this->likeService->hasLikedPost($request->user(), $postId);

        return response()->json([
            'liked' => $hasLiked,
        ]);
    }

    public function hasLikedComment(Request $request, int $commentId): JsonResponse
    {
        $hasLiked = $this->likeService->hasLikedComment($request->user(), $commentId);

        return response()->json([
            'liked' => $hasLiked,
        ]);
    }
}