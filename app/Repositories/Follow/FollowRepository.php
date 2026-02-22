<?php

namespace App\Repositories\Follow;

use App\Enums\FollowStatusEnum;
use App\Models\Follow;

interface FollowRepository
{
    public function all();
    public function find($id);
    public function create(array $data);
    public function update($model, array $data);
    public function delete($model);
    public function markAsPending($model);
    public function markAsAccepted($model);
    public function markAsRejected($model);
    public function getFollowers(int $userId, string $status = FollowStatusEnum::Accepted->value);
    public function getFollowees(int $userId, string $status = FollowStatusEnum::Accepted->value);
    public function findBetween(int $followerId, int $followeeId): ?Follow;
    public function countFollowers(int $userId): int;
    public function countFollowees(int $userId): int;
    public function existsBetween(int $followerId, int $followeeId): bool;
}
