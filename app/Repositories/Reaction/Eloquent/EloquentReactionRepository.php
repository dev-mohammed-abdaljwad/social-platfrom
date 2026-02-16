<?php

namespace App\Repositories\Reaction\Eloquent;

use App\Models\Reaction;
use Illuminate\Support\Facades\DB;
use App\Repositories\Reaction\ReactionRepository;

class EloquentReactionRepository implements ReactionRepository
{
    public function __construct(protected Reaction $model) {}

  

  

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
     public function findByReactable(string $reactableType, int $reactableId)
    {
        return $this->model->where('reactable_type', $reactableType)
            ->where('reactable_id', $reactableId)
            ->with('user')
            ->get();
    }
      public function findByUserAndReactable(int $userId, string $reactableType, int $reactableId)
    {
        return $this->model->where('user_id', $userId)
            ->where('reactable_type', $reactableType)
            ->where('reactable_id', $reactableId)
            ->first();
    }
       public function getCount(string $reactableType, int $reactableId)
    {
        return $this->model->where('reactable_type', $reactableType)
            ->where('reactable_id', $reactableId)
            ->select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->get();
    }

}