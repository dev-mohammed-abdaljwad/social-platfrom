<?php

namespace App\Repositories\Like;

use App\Models\User;

interface LikeRepository
{
    public function all();
    public function find($id);
    public function findByLikeable($likeableType, $likeableId);
    public function findByUser($userId);
    public function findByUserAndLikeable($userId, $likeableType, $likeableId);
    public function create(array $data);
    public function delete($model);
    public function toggle(User $user, string $likeableType, int $likeableId);
    public function hasLiked(User $user, string $likeableType, int $likeableId): bool;
}