<?php

namespace App\Repositories\Share;

interface ShareRepository
{
    public function all();
    public function find($id);
    public function findByUser($userId);
    public function findByPost($postId);
    public function create(array $data);
    public function update($model, array $data);
    public function delete($model);
}
