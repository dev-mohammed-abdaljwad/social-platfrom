<?php

namespace App\Repositories\Post;

use App\Models\User;

interface PostRepository
{
    public function all();
    public function find($id);
    public function findByUser($userId);
    public function getFeed(User $user, int $limit = 20);
    public function getPublicPosts(int $limit = 10);
    public function getPublicPostsPaginated(?int $lastId = null, int $limit = 10);
    public function getUserPostsWithRelations(User $user, bool $publicOnly = false);
    public function getUserSharedPosts(User $user);
    public function getSavedPostsForUser(User $user);
    public function create(array $data);
    public function update($model, array $data);
    public function delete($model);
}