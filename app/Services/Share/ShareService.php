<?php

namespace App\Services\Share;

use App\Models\User;
use App\Repositories\Share\ShareRepository;

class ShareService
{
    public function __construct(
        protected ShareRepository $repository
    ) {}

    public function all()
    {
        return $this->repository->all();
    }

    public function find($id)
    {
        return $this->repository->find($id);
    }

    public function findByUser($userId)
    {
        return $this->repository->findByUser($userId);
    }

    public function findByPost($postId)
    {
        return $this->repository->findByPost($postId);
    }

    public function create(array $data)
    {
        return $this->repository->create($data);
    }

    public function createForUser(User $user, int $postId, array $data)
    {
        return $this->repository->create([
            'user_id' => $user->id,
            'post_id' => $postId,
            'content' => $data['content'] ?? null,
        ]);
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
     * Toggle share on a post for a user.
     */
    public function toggleShare(User $user, int $postId, ?string $content = null): array
    {
        $existingShare = $this->repository->findByUserAndPost($user->id, $postId);

        if ($existingShare) {
            $this->repository->delete($existingShare);
            return [
                'shared' => false,
                'shares_count' => $this->repository->getSharesCount($postId),
            ];
        }

        $this->repository->create([
            'user_id' => $user->id,
            'post_id' => $postId,
            'content' => $content,
        ]);

        return [
            'shared' => true,
            'shares_count' => $this->repository->getSharesCount($postId),
        ];
    }

    /**
     * Get shares count for a post.
     */
    public function getSharesCount(int $postId): int
    {
        return $this->repository->getSharesCount($postId);
    }

    /**
     * Check if user can modify the share.
     */
    public function canModify(int $shareUserId, int $authUserId): bool
    {
        return $shareUserId === $authUserId;
    }

    /**
     * Format a share for JSON response.
     */
    public function formatShare($share): array
    {
        return [
            'id' => $share->id,
            'content' => $share->content,
        ];
    }
}
