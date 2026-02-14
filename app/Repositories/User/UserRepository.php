<?php

namespace App\Repositories\User;

use App\Models\User;

interface UserRepository
{
    public function all();
    public function find($id);
    public function findByEmail(string $email);
    public function findByUsername(string $username);
    public function create(array $data);
    public function update($model, array $data);
    public function delete($model);
    public function search(string $query, int $limit = 20);
    public function searchPaginated(string $query, int $perPage = 15, ?int $excludeUserId = null);
    public function getSuggestions(User $user, array $excludeIds, int $limit = 6);
}
