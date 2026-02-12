<?php

namespace App\Services\Comment;

use App\Repositories\Comment\CommentRepository;

class CommentService
{
    public function __construct(
        protected CommentRepository $repository
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
}
