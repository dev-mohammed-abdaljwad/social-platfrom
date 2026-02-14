<?php

namespace App\Services\Comment;

use App\Models\User;
use App\Repositories\Comment\CommentRepository;
use App\Services\Like\LikeService;

class CommentService
{
    public function __construct(
        protected CommentRepository $repository,
        protected LikeService $likeService
    ) {}

    public function all()
    {
        return $this->repository->all();
    }

    public function find($id)
    {
        return $this->repository->find($id);
    }

    public function findByPost($postId)
    {
        return $this->repository->findByPost($postId);
    }

    public function findRootCommentsByPost($postId)
    {
        return $this->repository->findRootCommentsByPost($postId);
    }

    public function findReplies($commentId)
    {
        return $this->repository->findReplies($commentId);
    }

    public function create(array $data)
    {
        return $this->repository->create($data);
    }

    public function update($model, array $data)
    {
        return $this->repository->update($model, $data);
    }

    public function delete($model)
    {
        return $this->repository->delete($model);
    }

    public function createForPost($postId, $userId, string $content, $parentId = null)
    {
        return $this->repository->create([
            'post_id' => $postId,
            'user_id' => $userId,
            'content' => $content,
            'parent_id' => $parentId,
        ]);
    }

    /**
     * Check if user can delete the comment.
     */
    public function canDelete(int $commentUserId, int $authUserId): bool
    {
        return $commentUserId === $authUserId;
    }

    /**
     * Get comments for a post formatted for response.
     */
    public function getCommentsForPost(int $postId, ?User $authUser = null): array
    {
        $comments = $this->findRootCommentsByPost($postId);

        return $comments->map(function ($comment) use ($authUser) {
            return $this->formatComment($comment, $authUser);
        })->toArray();
    }

    /**
     * Format a single comment for response.
     */
    public function formatComment($comment, ?User $authUser = null): array
    {
        $isOwner = $authUser && $authUser->id === $comment->user_id;
        $isLiked = $authUser && $this->likeService->hasLikedComment($authUser, $comment->id);

        return [
            'id' => $comment->id,
            'content' => $comment->content,
            'created_at' => $comment->created_at->diffForHumans(),
            'user' => [
                'id' => $comment->user->id,
                'name' => $comment->user->name,
                'avatar_url' => $comment->user->avatar_url,
            ],
            'is_owner' => $isOwner,
            'likes_count' => $comment->likes_count ?? $comment->likes()->count(),
            'is_liked' => $isLiked,
        ];
    }

    /**
     * Format a newly created comment for response.
     */
    public function formatNewComment($comment): array
    {
        $comment->load('user');

        return [
            'id' => $comment->id,
            'content' => $comment->content,
            'created_at' => $comment->created_at->diffForHumans(),
            'user' => [
                'name' => $comment->user->name,
                'avatar_url' => $comment->user->avatar_url,
            ],
            'likes_count' => 0,
            'is_liked' => false,
        ];
    }

    /**
     * Toggle like on a comment.
     */
    public function toggleLike(User $user, int $commentId): array
    {
        $result = $this->likeService->toggleCommentLike($user, $commentId);

        return [
            'liked' => $result['action'] === 'liked',
            'likes_count' => $this->repository->getLikesCount($commentId),
        ];
    }

    /**
     * Get likes count for a comment.
     */
    public function getLikesCount(int $commentId): int
    {
        return $this->repository->getLikesCount($commentId);
    }
}
