<?php

namespace App\Repositories\Reaction;

interface ReactionRepository
{

    public function create(array $data);
    public function update($model, array $data);
    public function delete($model);
    public  function findByReactable(string $reactableType, int $reactableId);
    public function findByUserAndReactable(int $userId, string $reactableType,int $reactableId);
    public function getCount(string $reactableType, int $reactableId);



}   