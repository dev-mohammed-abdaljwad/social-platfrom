<?php

namespace App\Repositories\User\Eloquent;

use App\Models\User;
use App\Repositories\User\UserRepository;

class EloquentUserRepository implements UserRepository
{
    public function __construct(protected User $model) {}

    public function all()
    {
        return $this->model->query()->where('is_active', true)->latest()->get();
    }

    public function find($id)
    {
        return $this->model->findOrFail($id);
    }

    public function findByEmail(string $email)
    {
        return $this->model->where('email', $email)->first();
    }

    public function findByUsername(string $username)
    {
        return $this->model->where('username', $username)->first();
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

    public function search(string $query, int $limit = 20)
    {
        return $this->model->where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('username', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%");
            })
            ->limit($limit)
            ->get();
    }
}
