<?php

namespace App\Repositories\Comment\Eloquent;

use App\Models\Comment;
use App\Repositories\Comment\CommentRepository;
use Illuminate\Support\Facades\Auth;

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

    public function findRootCommentsByPost($postId, int $limit = 20)
    {
        $userId = Auth::id();

        $query = $this->model->where('post_id', $postId)
            ->whereNull('parent_id')
            ->with([
                'user',
                'replies' => function ($query) use ($userId) {
                    $query->with('user')
                        ->withCount('reactions')
                        ->latest()
                        ->limit(3); // Only load first 3 replies, load more on demand
                    if ($userId) {
                        $query->withExists(['reactions as is_liked' => fn($q) => $q->where('user_id', $userId)]);
                    }
                }
            ])
            ->withCount('reactions', 'replies')
            ->latest()
            ->limit($limit);

        if ($userId) {
            $query->withExists(['reactions as is_liked' => fn($q) => $q->where('user_id', $userId)]);
        }

        return $query->get();
    }

    public function findReplies($commentId, int $limit = 10)
    {
        $userId = Auth::id();

        $query = $this->model->where('parent_id', $commentId)
            ->with('user')
            ->withCount('reactions')
            ->latest()
            ->limit($limit);

        if ($userId) {
            $query->withExists(['reactions as is_liked' => fn($q) => $q->where('user_id', $userId)]);
        }

        return $query->get();
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

    public function getReactionsCount($commentId): int
    {
        return $this->model->findOrFail($commentId)->reactions()->count();
    }
}
