<?php

namespace App\Services\Follow;

use App\Enums\FollowStatusEnum;
use App\Events\FollowCreated;
use App\Models\User;
use App\Repositories\Follow\FollowRepository;

class FollowService
{
    public function __construct(
        protected FollowRepository $repository
    ) {}

    /**
     * Follow a user.
     * - Public  accounts → immediately accepted
     * - Private accounts → pending until accepted
     */
    public function follow(User $follower, User $followee): array
    {
        if ($follower->id === $followee->id) {
            return ['success' => false, 'message' => 'You cannot follow yourself.'];
        }

        $existing = $this->repository->findBetween($follower->id, $followee->id);

        if ($existing) {
            $msg = $existing->status === FollowStatusEnum::Pending
                ? 'Follow request already pending.'
                : 'You are already following this user.';

            return ['success' => false, 'message' => $msg];
        }

        $status = ($followee->is_private ?? false)
            ? FollowStatusEnum::Pending
            : FollowStatusEnum::Accepted;

        $follow = $this->repository->create([
            'follower_id' => $follower->id,
            'followee_id' => $followee->id,
            'status'      => $status,
        ]);

        event(new FollowCreated($follower, $followee));

        $message = $status === FollowStatusEnum::Pending
            ? 'Follow request sent.'
            : 'You are now following this user.';

        return ['success' => true, 'message' => $message, 'follow' => $follow];
    }

    /**
     * Unfollow a user (works for both accepted & pending states).
     */
    public function unfollow(User $follower, User $followee): array
    {
        $follow = $this->repository->findBetween($follower->id, $followee->id);

        if (!$follow) {
            return ['success' => false, 'message' => 'You are not following this user.'];
        }

        $this->repository->delete($follow);

        return ['success' => true, 'message' => 'Unfollowed successfully.'];
    }

    /**
     * Cancel an outgoing pending follow request.
     */
    public function cancelRequest(User $follower, User $followee): array
    {
        $follow = $this->repository->findBetween($follower->id, $followee->id);

        if (!$follow || $follow->status !== FollowStatusEnum::Pending) {
            return ['success' => false, 'message' => 'No pending follow request found.'];
        }

        $this->repository->delete($follow);

        return ['success' => true, 'message' => 'Follow request cancelled.'];
    }

    /**
     * Accept an incoming follow request (called by the followee).
     */
    public function acceptRequest(User $followee, User $follower): array
    {
        $follow = $this->repository->findBetween($follower->id, $followee->id);

        if (!$follow) {
            return ['success' => false, 'message' => 'No follow request found.'];
        }

        if ($follow->status !== FollowStatusEnum::Pending) {
            return ['success' => false, 'message' => 'This follow request is not pending.'];
        }

        $this->repository->markAsAccepted($follow);

        return ['success' => true, 'message' => 'Follow request accepted.', 'follow' => $follow->fresh()];
    }

    /**
     * Decline an incoming follow request (called by the followee).
     */
    public function declineRequest(User $followee, User $follower): array
    {
        $follow = $this->repository->findBetween($follower->id, $followee->id);

        if (!$follow || $follow->status !== FollowStatusEnum::Pending) {
            return ['success' => false, 'message' => 'No pending follow request found.'];
        }

        $this->repository->delete($follow);

        return ['success' => true, 'message' => 'Follow request declined.'];
    }

    /**
     * Create a mutual accepted follow between two users (used for friendship auto-follow).
     * Silently skips if a follow already exists in either direction.
     */
    public function createMutualFollow(User $userA, User $userB): void
    {
        // A → B
        if (!$this->repository->existsBetween($userA->id, $userB->id)) {
            $this->repository->create([
                'follower_id' => $userA->id,
                'followee_id' => $userB->id,
                'status'      => FollowStatusEnum::Accepted,
            ]);
        } else {
            // If pending, accept it
            $existing = $this->repository->findBetween($userA->id, $userB->id);
            if ($existing && $existing->status === FollowStatusEnum::Pending) {
                $this->repository->markAsAccepted($existing);
            }
        }

        // B → A
        if (!$this->repository->existsBetween($userB->id, $userA->id)) {
            $this->repository->create([
                'follower_id' => $userB->id,
                'followee_id' => $userA->id,
                'status'      => FollowStatusEnum::Accepted,
            ]);
        } else {
            $existing = $this->repository->findBetween($userB->id, $userA->id);
            if ($existing && $existing->status === FollowStatusEnum::Pending) {
                $this->repository->markAsAccepted($existing);
            }
        }
    }

    /**
     * Remove mutual follow between two users (used for friendship auto-unfollow).
     */
    public function removeMutualFollow(User $userA, User $userB): void
    {
        // A → B
        $existingAB = $this->repository->findBetween($userA->id, $userB->id);
        if ($existingAB) {
            $this->repository->delete($existingAB);
        }

        // B → A
        $existingBA = $this->repository->findBetween($userB->id, $userA->id);
        if ($existingBA) {
            $this->repository->delete($existingBA);
        }
    }

    /**
     * Get paginated list of accepted followers for a user.
     */
    public function getFollowers(int $userId)
    {
        return $this->repository->getFollowers($userId);
    }

    /**
     * Get paginated list of users this user is following (accepted).
     */
    public function getFollowing(int $userId)
    {
        return $this->repository->getFollowees($userId);
    }

    /**
     * Get incoming pending follow requests for the authenticated user.
     */
    public function getFollowRequests(int $userId)
    {
        return $this->repository->getFollowers($userId, FollowStatusEnum::Pending->value);
    }

    /**
     * Get the follow relationship status between two users.
     */
    public function getStatus(User $from, User $to): array
    {
        $follow = $this->repository->findBetween($from->id, $to->id);

        if (!$follow) {
            return ['status' => 'none'];
        }

        return ['status' => $follow->status->value, 'follow_id' => $follow->id];
    }
}
