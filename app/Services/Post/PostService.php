<?php

namespace App\Services\Post;

use App\Models\User;
use App\Repositories\Post\PostRepository;

class PostService
{
    public function __construct(
        protected PostRepository $repository
    ) {}

    public function all()
    {
        return $this->repository->all();
    }

    public function find($id)
    {
        return $this->repository->find($id);
    }

    public function findByUser($userId)
    {
        return $this->repository->findByUser($userId);
    }

    public function getFeed(User $user, int $limit = 20)
    {
        return $this->repository->getFeed($user, $limit);
    }

    public function create(array $data)
    {
        return $this->repository->create($data);
    }

    public function createForUser(User $user, array $data)
    {
        return $this->repository->create([
            'user_id' => $user->id,
            'content' => $data['content'],
            'image' => $data['image'] ?? null,
            'video' => $data['video'] ?? null,
            'location' => $data['location'] ?? null,
            'privacy' => $data['privacy'] ?? 'public',
            'type' => $data['type'] ?? 'text',
        ]);
    }

    public function update($model, array $data)
    {
        return $this->repository->update($model, $data);
    }

    public function delete($model)
    {
        return $this->repository->delete($model);
    }
}