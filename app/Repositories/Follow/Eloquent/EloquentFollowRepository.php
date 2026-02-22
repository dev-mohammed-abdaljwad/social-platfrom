<?php

namespace App\Repositories\Follow\Eloquent;

use App\Enums\FollowStatusEnum;
use App\Models\Follow;
use App\Repositories\Follow\FollowRepository;

class EloquentFollowRepository implements FollowRepository
{
    public function __construct(
        protected Follow $model
    ) {}

    public function all()
    {
        return $this->model->latest()->get();
    }

    public function find($id)
    {
        return $this->model->findOrFail($id);
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

    public function markAsPending($model)
    {
        return $model->update([
            'status' => FollowStatusEnum::Pending,
        ]);
    }
    public function markAsAccepted($model)
    {
        return $model->update([
            'status' => FollowStatusEnum::Accepted,
        ]);
    }
    public function markAsRejected($model)
    {
        return $model->update([
            'status' => FollowStatusEnum::Rejected,
        ]);
    }
    public function getFollowers(int $userId, string $status = FollowStatusEnum::Accepted->value)
    {
        return $this->model
            ->where('followee_id', $userId)
            ->where('status', $status)
            ->with('follower')
            ->paginate(20);
    }
    public function getFollowees(int $userId, string $status = FollowStatusEnum::Accepted->value)
    {
        return $this->model
            ->where('follower_id', $userId)
            ->where('status', $status)
            ->with('followee')
            ->paginate(20);
    }
    public function findBetween(int $followerId, int $followeeId): ?Follow
    {
        return $this->model
            ->where('follower_id', $followerId)
            ->where('followee_id', $followeeId)
            ->first();
    }
    public function countFollowers(int $userId): int
    {
        return $this->model
            ->where('followee_id', $userId)
            ->where('status', FollowStatusEnum::Accepted)
            ->count();
    }

    public function countFollowees(int $userId): int
    {
        return $this->model
            ->where('follower_id', $userId)
            ->where('status', FollowStatusEnum::Accepted)
            ->count();
    }
    public function existsBetween(int $followerId, int $followeeId): bool
    {
        return $this->model
            ->where('follower_id', $followerId)
            ->where('followee_id', $followeeId)
            ->exists();
    }
}
