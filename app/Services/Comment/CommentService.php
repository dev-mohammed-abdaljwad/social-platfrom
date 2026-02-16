<?php

namespace App\Services\Comment;

use App\Models\User;
use App\Models\Comment;
use App\Repositories\Comment\CommentRepository;
use Illuminate\Support\Facades\DB;

class CommentService
{
    public function __construct(
        protected CommentRepository $repository
    ) {}

    public function all()
    {
        return $this->repository->all();
    }

    public function find($id)
    {
        return $this->repository->find($id);
    }

    public function findByPost($postId)
    {
        return $this->repository->findByPost($postId);
    }

    public function findRootCommentsByPost($postId)
    {
        return $this->repository->findRootCommentsByPost($postId);
    }

    public function findReplies($commentId)
    {
        return $this->repository->findReplies($commentId);
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

    public function createForPost($postId, $userId, string $content, $parentId = null)
    {
        return $this->repository->create([
            'post_id' => $postId,
            'user_id' => $userId,
            'content' => $content,
            'parent_id' => $parentId,
        ]);
    }

    /**
     * Check if user can delete the comment.
     */
    public function canDelete(int $commentUserId, int $authUserId): bool
    {
        return $commentUserId === $authUserId;
    }

    /**
     * Get comments for a post formatted for response (WITH REACTIONS).
     */
    public function getCommentsForPost(int $postId, ?User $authUser = null): array
    {
        $comments = $this->findRootCommentsByPost($postId)
            ->load('reactions','user'); // Load reactions and user data

        return $comments->map(function ($comment) use ($authUser) {
            return $this->formatComment($comment, $authUser);
        })->toArray();
    }

    /**
     * Format a single comment for response.
     * Updated to include reaction data.
     */
    public function formatComment($comment, ?User $authUser = null): array
    {
        $isOwner = $authUser && $authUser->id === $comment->user_id;
        
        // Get user's reaction on this comment
        $userReaction = null;
        $reactionCounts = [];

        if ($authUser) {
            $userReaction = $comment->reactions()
                ->where('user_id', $authUser->id)
                ->first();

            // Get all reaction counts grouped by type
            $reactionCounts = $comment->reactions()
               ->select('type', DB::raw('count(*) as count'))
                    ->groupBy('type')
                    ->get()
                    ->pluck('count', 'type')
                    ->toArray();
        }

        return [
            'id' => $comment->id,
            'content' => htmlspecialchars($comment->content),
            'created_at' => $comment->created_at->diffForHumans(),
            'user' => [
                'id' => $comment->user->id,
                'name' => $comment->user->name,
                'avatar_url' => $comment->user->avatar_url,
            ],
            'is_owner' => $isOwner,
            // NEW: Reaction data
            'user_reaction' => $userReaction?->type,
            'reaction_counts' => $reactionCounts,
        ];
    }

    /**
     * Format a newly created comment for response.
     * Updated to include reaction data.
     */
    public function formatNewComment($comment, ?User $authUser = null): array
    {
        $comment->load('user', 'reactions');
        
        // New comment has no reactions yet
        $userReaction = null;
        $reactionCounts = [];

        if ($authUser) {
            $userReaction = $comment->reactions()
                ->where('user_id', $authUser->id)
                ->first();

            $reactionCounts = $comment->reactions()
                ->select('type', DB::raw('count(*) as count'))
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray();
        }

        return [
            'id' => $comment->id,
            'content' => htmlspecialchars($comment->content),
            'created_at' => $comment->created_at->diffForHumans(),
            'user' => [
                'id' => $comment->user->id,
                'name' => $comment->user->name,
                'avatar_url' => $comment->user->avatar_url,
            ],
            'is_owner' => $authUser && $authUser->id === $comment->user_id,
            // NEW: Reaction data
            'user_reaction' => $userReaction?->type,
            'reaction_counts' => $reactionCounts,
        ];
    }

    /**
     * Get comments with reactions for a specific post
     * Alias for getCommentsForPost (for clarity)
     */
    public function getCommentsWithReactions(int $postId, ?User $authUser = null): array
    {
        return $this->getCommentsForPost($postId, $authUser);
    }
}