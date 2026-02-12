<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Post\CreatePostRequest;
use App\Http\Requests\Api\V1\Post\UpdatePostRequest;
use App\Services\Post\PostService;
use App\Transformers\Post\PostTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Posts
 *
 * APIs for managing posts
 */
class PostController extends Controller
{
    public function __construct(
        protected PostService $postService
    ) {}

    /**
     * Get feed
     *
     * Get the authenticated user's feed (own posts + friends' posts).
     *
     * @queryParam limit integer Number of posts to return. Default: 20. Example: 20
     *
     * @response {
     *   "data": [
     *     {
     *       "id": 1,
     *       "content": "Hello world!",
     *       "content_type": "text",
     *       "privacy": "public",
     *       "user": {"id": 1, "name": "John Doe"},
     *       "likes_count": 5,
     *       "comments_count": 2,
     *       "created_at": "2026-02-12T10:00:00.000000Z"
     *     }
     *   ]
     * }
     */
    public function feed(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 20);
        $posts = $this->postService->getFeed($request->user(), $limit);

        return response()->json([
            'data' => PostTransformer::collection($posts),
        ]);
    }

    /**
     * List all public posts
     *
     * Get all public posts (paginated).
     *
     * @response {
     *   "data": [
     *     {
     *       "id": 1,
     *       "content": "Hello world!",
     *       "content_type": "text",
     *       "privacy": "public",
     *       "user": {"id": 1, "name": "John Doe"},
     *       "likes_count": 5,
     *       "comments_count": 2,
     *       "created_at": "2026-02-12T10:00:00.000000Z"
     *     }
     *   ]
     * }
     */
    public function index(): JsonResponse
    {
        $posts = $this->postService->all();

        return response()->json([
            'data' => PostTransformer::collection($posts),
        ]);
    }

    /**
     * Create a post
     *
     * Create a new post for the authenticated user.
     *
     * @bodyParam content string required The post content. Example: Hello world!
     * @bodyParam content_type string The content type (text, image, video). Default: text. Example: text
     * @bodyParam privacy string The privacy level (public, private, friends). Default: public. Example: public
     * @bodyParam media_url string The media URL for image/video posts. Example: https://example.com/image.jpg
     *
     * @response 201 {
     *   "message": "Post created successfully",
     *   "data": {
     *     "id": 1,
     *     "content": "Hello world!",
     *     "content_type": "text",
     *     "privacy": "public",
     *     "user": {"id": 1, "name": "John Doe"},
     *     "created_at": "2026-02-12T10:00:00.000000Z"
     *   }
     * }
     */
    public function store(CreatePostRequest $request): JsonResponse
    {
        $post = $this->postService->createForUser(
            $request->user(),
            $request->validated()
        );

        return response()->json([
            'message' => 'Post created successfully',
            'data' => new PostTransformer($post),
        ], 201);
    }

    /**
     * Get a post
     *
     * Get a specific post by ID.
     *
     * @urlParam id integer required The post ID. Example: 1
     *
     * @response {
     *   "data": {
     *     "id": 1,
     *     "content": "Hello world!",
     *     "content_type": "text",
     *     "privacy": "public",
     *     "user": {"id": 1, "name": "John Doe"},
     *     "likes_count": 5,
     *     "comments_count": 2,
     *     "created_at": "2026-02-12T10:00:00.000000Z"
     *   }
     * }
     * @response 404 {
     *   "message": "Post not found"
     * }
     */
    public function show(int $id): JsonResponse
    {
        $post = $this->postService->find($id);

        return response()->json([
            'data' => new PostTransformer($post),
        ]);
    }

    /**
     * Update a post
     *
     * Update an existing post (must be the owner).
     *
     * @urlParam id integer required The post ID. Example: 1
     *
     * @bodyParam content string The updated content. Example: Updated content!
     * @bodyParam privacy string The privacy level (public, private, friends). Example: friends
     *
     * @response {
     *   "message": "Post updated successfully",
     *   "data": {
     *     "id": 1,
     *     "content": "Updated content!",
     *     "privacy": "friends"
     *   }
     * }
     * @response 403 {
     *   "message": "Unauthorized to update this post"
     * }
     */
    public function update(UpdatePostRequest $request, int $id): JsonResponse
    {
        $post = $this->postService->find($id);

        // Check ownership
        if ($post->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized to update this post',
            ], 403);
        }

        $updated = $this->postService->update($post, $request->validated());

        return response()->json([
            'message' => 'Post updated successfully',
            'data' => new PostTransformer($updated),
        ]);
    }

    /**
     * Delete a post
     *
     * Delete a post (must be the owner).
     *
     * @urlParam id integer required The post ID. Example: 1
     *
     * @response {
     *   "message": "Post deleted successfully"
     * }
     * @response 403 {
     *   "message": "Unauthorized to delete this post"
     * }
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $post = $this->postService->find($id);

        // Check ownership
        if ($post->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized to delete this post',
            ], 403);
        }

        $this->postService->delete($post);

        return response()->json([
            'message' => 'Post deleted successfully',
        ]);
    }

    /**
     * Get user's posts
     *
     * Get all posts from a specific user.
     *
     * @urlParam userId integer required The user ID. Example: 1
     *
     * @response {
     *   "data": [
     *     {
     *       "id": 1,
     *       "content": "Hello world!",
     *       "user": {"id": 1, "name": "John Doe"}
     *     }
     *   ]
     * }
     */
    public function userPosts(int $userId): JsonResponse
    {
        $posts = $this->postService->findByUser($userId);

        return response()->json([
            'data' => PostTransformer::collection($posts),
        ]);
    }
}
