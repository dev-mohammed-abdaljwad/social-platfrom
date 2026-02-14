<?php

namespace App\Repositories\Comment\Eloquent;

use App\Models\Comment;
use App\Repositories\Comment\CommentRepository;

class EloquentCommentRepository implements CommentRepository
{
    public function __construct(protected Comment $model) {}

    public function all()
    {
        return $this->model->query()->with(['user', 'post'])->latest()->get();
    }

    public function find($id)
    {
        return $this->model->with(['user', 'post', 'replies.user'])->findOrFail($id);
    }

    public function findByPost($postId)
    {
        return $this->model->where('post_id', $postId)
            ->with(['user', 'replies.user'])
            ->latest()
            ->get();
    }

    public function findRootCommentsByPost($postId)
    {
        return $this->model->where('post_id', $postId)
            ->whereNull('parent_id')
            ->with(['user', 'replies.user', 'likes'])
            ->withCount('likes', 'replies')
            ->latest()
            ->get();
    }

    public function findReplies($commentId)
    {
        return $this->model->where('parent_id', $commentId)
            ->with(['user', 'likes'])
            ->withCount('likes')
            ->latest()
            ->get();
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($model, array $data)
    {
        $model->update($data);
        return $model;
    }

    public function delete($model)
    {
        return $model->delete();
    }

    public function getLikesCount($commentId): int
    {
        return $this->model->findOrFail($commentId)->likes()->count();
    }
}
