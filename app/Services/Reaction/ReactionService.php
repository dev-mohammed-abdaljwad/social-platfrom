<?php

namespace App\Services\Reaction;
use App\Enums\ReactionTypeEnum;
use App\Models\Post;
use App\Models\Comment;
use App\Models\User;
use App\Repositories\Comment\CommentRepository;
use App\Repositories\Post\PostRepository;
use App\Repositories\Reaction\ReactionRepository;
use Illuminate\Database\Eloquent\Model;

class ReactionService
{
    public function __construct(
        protected ReactionRepository $repository,
        protected PostRepository $postRepository,
        protected CommentRepository $commentRepository 
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
    
    /**
     * Generic react method for any reactable model (Post, Comment, etc)
     */
    public function reactToModel(User $user, Model $reactable, string $type)
    {
        $reactionType = ReactionTypeEnum::from($type);
        
        // Find existing reaction
        $existing = $reactable->reactions()
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            if ($existing->type->value === $type) {
                // Unreact - remove the reaction
                $this->repository->delete($existing);
                return [
                    'action' => 'removed',
                    'reaction' => null,
                    'counts' => $this->getReactionCounts($reactable)
                ];
            } else {
                // Update to new reaction type
                $this->repository->update($existing, ['type' => $reactionType]);
                return [
                    'action' => 'updated',
                    'reaction' => $existing,
                    'counts' => $this->getReactionCounts($reactable)
                ];
            }
        }

        // Create new reaction
        $reaction = $reactable->reactions()->create([
            'user_id' => $user->id,
            'type' => $reactionType,
        ]);

        return [
            'action' => 'added',
            'reaction' => $reaction,
            'counts' => $this->getReactionCounts($reactable)
        ];
    }

    /**
     * Specific method for posts (keeping backward compatibility)
     */
    public function reactToPost(User $user, int $postId, string $type)
    {
        $post = $this->postRepository->find($postId);
        if (!$post) {
            return ['error' => 'Post not found'];
        }
        return $this->reactToModel($user, $post, $type);
    }

    /**
     * New method for comments
     */
    public function reactToComment(User $user, int $commentId, string $type)
    {
        $comment = $this->commentRepository->find($commentId);
        if (!$comment) {
            return ['error' => 'Comment not found'];
        }
        return $this->reactToModel($user, $comment, $type);
    }

    /**
     * Get reaction counts for any model
     */
    public function getReactionCounts(Model $reactable)
    {
        $counts = $reactable->reactions()
            ->select('type', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
            ->groupBy('type')
            ->get();

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

    /**
     * Old method for posts - kept for backward compatibility
     */
    public function getPostReactionCounts(int $postId)
    {
        $post = $this->postRepository->find($postId);
        if (!$post) return ['detailed' => [], 'total' => 0];
        return $this->getReactionCounts($post);
    }

    public function getUserReactionOnPost(int $userId, int $postId)
    {
        return $this->repository->findByUserAndReactable($userId, Post::class, $postId);
    }
}