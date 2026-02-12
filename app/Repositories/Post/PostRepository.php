<?php

namespace App\Repositories\Post;

use App\Models\User;

interface PostRepository
{
    public function all();
    public function find($id);
    public function findByUser($userId);
    public function getFeed(User $user, int $limit = 20);
    public function create(array $data);
    public function update($model, array $data);
    public function delete($model);
}