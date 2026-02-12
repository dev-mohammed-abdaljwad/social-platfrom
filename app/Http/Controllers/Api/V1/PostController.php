<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\Post\CreatePostRequest;
use App\Http\Requests\Api\V1\Post\UpdatePostRequest;
use App\Services\Post\PostService;
use App\Transformers\Post\PostTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function __construct(
        protected PostService $postService
    ) {}

    public function feed(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 20);
        $posts = $this->postService->getFeed($request->user(), $limit);

        return response()->json([
            'data' => PostTransformer::collection($posts),
        ]);
    }

    public function index(): JsonResponse
    {
        $posts = $this->postService->all();

        return response()->json([
            'data' => PostTransformer::collection($posts),
        ]);
    }

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

    public function show(int $id): JsonResponse
    {
        $post = $this->postService->find($id);

        return response()->json([
            'data' => new PostTransformer($post),
        ]);
    }

    public function update(UpdatePostRequest $request, int $id): JsonResponse
    {
        $post = $this->postService->find($id);

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

    public function destroy(Request $request, int $id): JsonResponse
    {
        $post = $this->postService->find($id);

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

    public function userPosts(int $userId): JsonResponse
    {
        $posts = $this->postService->findByUser($userId);

        return response()->json([
            'data' => PostTransformer::collection($posts),
        ]);
    }
}
