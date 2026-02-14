<?php

namespace App\Services\Like;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Repositories\Like\LikeRepository;

class LikeService
{
    public function __construct(
        protected LikeRepository $repository
    ) {}

    public function all()
    {
        return $this->repository->all();
    }

    public function find($id)
    {
        return $this->repository->find($id);
    }

    public function findByLikeable($likeableType, $likeableId)
    {
        return $this->repository->findByLikeable($likeableType, $likeableId);
    }

    public function findByUser($userId)
    {
        return $this->repository->findByUser($userId);
    }

    public function togglePostLike(User $user, int $postId)
    {
        $result = $this->repository->toggle($user, Post::class, $postId);

        return [
            'liked' => $result['action'] === 'liked',
            'likes_count' => $this->getPostLikesCount($postId),
            'like' => $result['like'],
        ];
    }

    public function toggleCommentLike(User $user, int $commentId)
    {
        $result = $this->repository->toggle($user, Comment::class, $commentId);

        return [
            'liked' => $result['action'] === 'liked',
            'likes_count' => $this->getCommentLikesCount($commentId),
            'like' => $result['like'],
        ];
    }

    public function findByUserAndPost($userId, $postId)
    {
        return $this->repository->findByUserAndLikeable($userId, Post::class, $postId);
    }

    public function findByUserAndComment($userId, $commentId)
    {
        return $this->repository->findByUserAndLikeable($userId, Comment::class, $commentId);
    }

    public function hasLikedPost(User $user, int $postId): bool
    {
        return $this->repository->hasLiked($user, Post::class, $postId);
    }

    public function hasLikedComment(User $user, int $commentId): bool
    {
        return $this->repository->hasLiked($user, Comment::class, $commentId);
    }

    /**
     * Get likes count for a post.
     */
    public function getPostLikesCount(int $postId): int
    {
        return count($this->repository->findByLikeable(Post::class, $postId));
    }

    /**
     * Get likes count for a comment.
     */
    public function getCommentLikesCount(int $commentId): int
    {
        return count($this->repository->findByLikeable(Comment::class, $commentId));
    }
}
