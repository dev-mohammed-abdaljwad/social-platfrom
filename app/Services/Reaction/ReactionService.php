<?php

namespace App\Services\Reaction;

use App\Enums\ReactionTypeEnum;
use App\Models\Post;
use App\Models\User;
use App\Repositories\Reaction\ReactionRepository;

class ReactionService
{
    public function __construct(
        protected ReactionRepository $repository
    ) {}
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
    
    public function reactToPost(User $user, int $postId, string $type)
    {
        $reactionType = ReactionTypeEnum::from($type);
        $existing = $this->repository->findByUserAndReactable($user->id, Post::class, $postId);

        if ($existing) {
            if ($existing->type->value === $type) {
                $this->repository->delete($existing);
                return [
                    'action' => 'removed',
                    'reaction' => null,
                    'counts' => $this->getPostReactionCounts($postId)
                ];
            } else {
                $this->repository->update($existing, ['type' => $reactionType]);
                return [
                    'action' => 'updated',
                    'reaction' => $existing,
                    'counts' => $this->getPostReactionCounts($postId)
                ];
            }
        }

        $reaction = $this->repository->create([
            'user_id' => $user->id,
            'reactable_type' => Post::class,
            'reactable_id' => $postId,
            'type' => $reactionType,
        ]);

        return [
            'action' => 'added',
            'reaction' => $reaction,
            'counts' => $this->getPostReactionCounts($postId)
        ];
    }
         public function getPostReactionCounts(int $postId)
    {
        $counts = $this->repository->getCount(Post::class, $postId);
        $formatted = [];
        $total = 0;

        foreach ($counts as $item) {
            $formatted[$item->type->value] = $item->count;
            $total += $item->count;
        }

        return [
            'detailed' => $formatted,
            'total' => $total
        ];
    }
        public function getUserReactionOnPost(int $userId, int $postId)
            {
                return $this->repository->findByUserAndReactable($userId, Post::class, $postId);
            }
}