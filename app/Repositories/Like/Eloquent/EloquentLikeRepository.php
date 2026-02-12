<?php

namespace App\Repositories\Like\Eloquent;

use App\Models\Like;
use App\Models\User;
use App\Repositories\Like\LikeRepository;

class EloquentLikeRepository implements LikeRepository
{
    public function __construct(protected Like $model) {}

    public function all()
    {
        return $this->model->query()->with(['user', 'likeable'])->latest()->get();
    }

    public function find($id)
    {
        return $this->model->with(['user', 'likeable'])->findOrFail($id);
    }

    public function findByLikeable($likeableType, $likeableId)
    {
        return $this->model->where('likeable_type', $likeableType)
            ->where('likeable_id', $likeableId)
            ->with('user')
            ->get();
    }

    public function findByUser($userId)
    {
        return $this->model->where('user_id', $userId)
            ->with('likeable')
            ->latest()
            ->get();
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function delete($model)
    {
        return $model->delete();
    }

    public function toggle(User $user, string $likeableType, int $likeableId)
    {
        $existing = $this->model->where('user_id', $user->id)
            ->where('likeable_type', $likeableType)
            ->where('likeable_id', $likeableId)
            ->first();

        if ($existing) {
            $existing->delete();
            return ['action' => 'unliked', 'like' => null];
        }

        $like = $this->create([
            'user_id' => $user->id,
            'likeable_type' => $likeableType,
            'likeable_id' => $likeableId,
        ]);

        return ['action' => 'liked', 'like' => $like];
    }

    public function hasLiked(User $user, string $likeableType, int $likeableId): bool
    {
        return $this->model->where('user_id', $user->id)
            ->where('likeable_type', $likeableType)
            ->where('likeable_id', $likeableId)
            ->exists();
    }
}
