<?php

namespace App\Services\Comment;

use App\Models\User;
use App\Models\Comment;
use App\Repositories\Comment\CommentRepository;
use App\Services\Mentions\MentionsService;
use Illuminate\Support\Facades\DB;

class CommentService
{
    public function __construct(
        protected CommentRepository $repository,
        protected MentionsService $mentionsService
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
        $comment = $this->repository->create($data);
        if (!empty($data['content'])) {
            $this->mentionsService->handleCreated($comment, $data['content'], $data['user_id'] ?? $comment->user_id);
        }
        return $comment;
    }

    public function update($model, array $data)
    {
        $comment = $this->repository->update($model, $data);
        if (isset($data['content'])) {
            $this->mentionsService->handleUpdated($comment, $data['content'], $comment->user_id);
        }
        return $comment;
    }

    public function delete($model)
    {
        $this->mentionsService->handleDeleted($model);
        return $this->repository->delete($model);
    }

    public function createForPost($postId, $userId, string $content, $parentId = null)
    {
        $comment = $this->repository->create([
            'post_id' => $postId,
            'user_id' => $userId,
            'content' => $content,
            'parent_id' => $parentId,
        ]);

        if (!empty($content)) {
            $this->mentionsService->handleCreated($comment, $content, $userId);
        }

        return $comment;
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
            ->loadMissing(['reactions', 'user', 'mentions.mentionedUser', 'replies.reactions', 'replies.user', 'replies.mentions.mentionedUser']);

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
            // Use already loaded collection to avoid N+1
            $userReaction = $comment->reactions
                ->where('user_id', $authUser->id)
                ->first();

            // Use already loaded collection to count grouped by type
            $reactionCounts = $comment->reactions
                ->groupBy('type')
                ->map(fn($group) => $group->count())
                ->toArray();
        }

        return [
            'id' => $comment->id,
            'parent_id' => $comment->parent_id,
            'content' => $this->mentionsService->render($comment->content, $comment->mentions->pluck('mentionedUser')),
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
            'replies_count' => $comment->replies_count ?? 0,
            'replies' => $comment->relationLoaded('replies') ? $comment->replies->map(function ($reply) use ($authUser) {
                return $this->formatComment($reply, $authUser);
            })->toArray() : [],
        ];
    }

    /**
     * Format a newly created comment for response.
     * Updated to include reaction data.
     */
    public function formatNewComment($comment, ?User $authUser = null): array
    {
        if ($authUser && $comment->user_id === $authUser->id) {
            $comment->setRelation('user', $authUser);
        }

        $comment->loadMissing(['user', 'reactions', 'mentions.mentionedUser']);

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
            'parent_id' => $comment->parent_id,
            'content' => $this->mentionsService->render($comment->content, $comment->mentions->pluck('mentionedUser')),
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
            'replies_count' => 0,
            'replies' => [],
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
