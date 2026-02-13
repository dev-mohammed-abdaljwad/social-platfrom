<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\Share\CreateShareRequest;
use App\Http\Requests\Api\V1\Share\UpdateShareRequest;
use App\Services\Post\PostService;
use App\Services\Share\ShareService;
use App\Transformers\Share\ShareTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShareController extends Controller
{
    public function __construct(
        protected ShareService $shareService,
        protected PostService $postService
    ) {}

    /**
     * Get all shares for a post.
     */
    public function index(int $postId): JsonResponse
    {
        $shares = $this->shareService->findByPost($postId);

        return response()->json([
            'data' => ShareTransformer::collection($shares),
        ]);
    }

    /**
     * Share a post.
     */
    public function store(CreateShareRequest $request, int $postId): JsonResponse
    {
        // Verify the post exists
        $this->postService->find($postId);

        $share = $this->shareService->createForUser(
            $request->user(),
            $postId,
            $request->validated()
        );

        return response()->json([
            'message' => 'Post shared successfully',
            'data' => new ShareTransformer($share->load(['user', 'post.user'])),
        ], 201);
    }

    /**
     * Get a specific share.
     */
    public function show(int $id): JsonResponse
    {
        $share = $this->shareService->find($id);

        return response()->json([
            'data' => new ShareTransformer($share),
        ]);
    }

    /**
     * Update a share (edit the share content/comment).
     */
    public function update(UpdateShareRequest $request, int $id): JsonResponse
    {
        $share = $this->shareService->find($id);

        if ($share->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized to update this share',
            ], 403);
        }

        $updated = $this->shareService->update($share, $request->validated());

        return response()->json([
            'message' => 'Share updated successfully',
            'data' => new ShareTransformer($updated),
        ]);
    }

    /**
     * Delete a share.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $share = $this->shareService->find($id);

        if ($share->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized to delete this share',
            ], 403);
        }

        $this->shareService->delete($share);

        return response()->json([
            'message' => 'Share deleted successfully',
        ]);
    }

    /**
     * Get all shares by the authenticated user.
     */
    public function myShares(Request $request): JsonResponse
    {
        $shares = $this->shareService->findByUser($request->user()->id);

        return response()->json([
            'data' => ShareTransformer::collection($shares),
        ]);
    }
}
