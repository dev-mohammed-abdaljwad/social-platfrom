<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\Comment\CreateCommentRequest;
use App\Http\Requests\Api\V1\Comment\UpdateCommentRequest;
use App\Services\Comment\CommentService;
use App\Transformers\Comment\CommentTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function __construct(
        protected CommentService $commentService
    ) {}

    public function index(int $postId): JsonResponse
    {
        $comments = $this->commentService->findRootCommentsByPost($postId);

        return response()->json([
            'data' => CommentTransformer::collection($comments),
        ]);
    }

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

    public function show(int $postId, int $commentId): JsonResponse
    {
        $comment = $this->commentService->find($commentId);

        return response()->json([
            'data' => new CommentTransformer($comment),
        ]);
    }

    public function update(UpdateCommentRequest $request, int $postId, int $commentId): JsonResponse
    {
        $comment = $this->commentService->find($commentId);

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

    public function destroy(Request $request, int $postId, int $commentId): JsonResponse
    {
        $comment = $this->commentService->find($commentId);

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

    public function replies(int $postId, int $commentId): JsonResponse
    {
        $replies = $this->commentService->findReplies($commentId);

        return response()->json([
            'data' => CommentTransformer::collection($replies),
        ]);
    }
}
