<?php

namespace App\Repositories\Share\Eloquent;

use App\Models\Share;
use App\Repositories\Share\ShareRepository;

class EloquentShareRepository implements ShareRepository
{
    public function __construct(protected Share $model) {}

    public function all()
    {
        return $this->model->query()
            ->with(['user', 'post.user'])
            ->latest()
            ->get();
    }

    public function find($id)
    {
        return $this->model
            ->with(['user', 'post.user'])
            ->findOrFail($id);
    }

    public function findByUser($userId)
    {
        return $this->model->where('user_id', $userId)
            ->with(['user', 'post.user'])
            ->latest()
            ->get();
    }

    public function findByPost($postId)
    {
        return $this->model->where('post_id', $postId)
            ->with(['user'])
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
        return $model->fresh(['user', 'post.user']);
    }

    public function delete($model)
    {
        return $model->delete();
    }
}
