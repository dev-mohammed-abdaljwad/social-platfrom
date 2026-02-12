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
        return $this->repository->toggle($user, Post::class, $postId);
    }

    public function toggleCommentLike(User $user, int $commentId)
    {
        return $this->repository->toggle($user, Comment::class, $commentId);
    }

    public function hasLikedPost(User $user, int $postId): bool
    {
        return $this->repository->hasLiked($user, Post::class, $postId);
    }

    public function hasLikedComment(User $user, int $commentId): bool
    {
        return $this->repository->hasLiked($user, Comment::class, $commentId);
    }
}
