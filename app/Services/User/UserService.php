<?php

namespace App\Services\User;

use App\Repositories\User\UserRepository;

class UserService
{
    public function __construct(
        protected UserRepository $repository
    ) {}

    public function all()
    {
        return $this->repository->all();
    }

    public function find($id)
    {
        return $this->repository->find($id);
    }

    public function findByEmail(string $email)
    {
        return $this->repository->findByEmail($email);
    }

    public function findByUsername(string $username)
    {
        return $this->repository->findByUsername($username);
    }

    public function create(array $data)
    {
        return $this->repository->create($data);
    }

    public function update($model, array $data)
    {
        return $this->repository->update($model, $data);
    }

    public function delete($model)
    {
        return $this->repository->delete($model);
    }

    public function search(string $query, int $limit = 20)
    {
        return $this->repository->search($query, $limit);
    }

    public function updateProfile($user, array $data)
    {
        $allowedFields = ['name', 'bio', 'phone', 'profile_picture'];
        $filteredData = array_intersect_key($data, array_flip($allowedFields));
        
        return $this->repository->update($user, $filteredData);
    }
}
