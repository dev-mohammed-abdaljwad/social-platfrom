<?php

namespace App\Services\Friendship;

use App\Models\User;
use App\Repositories\Friendship\FriendshipRepository;

class FriendshipService
{
    public function __construct(
        protected FriendshipRepository $repository
    ) {}

    public function all()
    {
        return $this->repository->all();
    }

    public function find($id)
    {
        return $this->repository->find($id);
    }

    public function getPendingRequestsFor(User $user)
    {
        return $this->repository->getPendingRequestsFor($user);
    }

    public function getSentRequestsBy(User $user)
    {
        return $this->repository->getSentRequestsBy($user);
    }

    public function getFriendsOf(User $user)
    {
        return $this->repository->getFriendsOf($user);
    }

    public function sendRequest(User $sender, User $receiver)
    {
        if ($sender->id === $receiver->id) {
            return ['success' => false, 'message' => 'Cannot send friend request to yourself'];
        }

        return $this->repository->sendRequest($sender, $receiver);
    }

    public function acceptRequest($friendshipId, User $user)
    {
        $friendship = $this->repository->find($friendshipId);

        if ($friendship->receiver_id !== $user->id) {
            return ['success' => false, 'message' => 'Unauthorized to accept this request'];
        }

        if (!$friendship->isPending()) {
            return ['success' => false, 'message' => 'This request is no longer pending'];
        }

        $this->repository->acceptRequest($friendship);
        
        return ['success' => true, 'message' => 'Friend request accepted', 'friendship' => $friendship->fresh()];
    }

    public function rejectRequest($friendshipId, User $user)
    {
        $friendship = $this->repository->find($friendshipId);

        if ($friendship->receiver_id !== $user->id) {
            return ['success' => false, 'message' => 'Unauthorized to reject this request'];
        }

        if (!$friendship->isPending()) {
            return ['success' => false, 'message' => 'This request is no longer pending'];
        }

        $this->repository->rejectRequest($friendship);
        
        return ['success' => true, 'message' => 'Friend request rejected'];
    }

    public function cancelRequest($friendshipId, User $user)
    {
        $friendship = $this->repository->find($friendshipId);

        if ($friendship->sender_id !== $user->id) {
            return ['success' => false, 'message' => 'Unauthorized to cancel this request'];
        }

        if (!$friendship->isPending()) {
            return ['success' => false, 'message' => 'This request is no longer pending'];
        }

        $this->repository->delete($friendship);
        
        return ['success' => true, 'message' => 'Friend request cancelled'];
    }

    public function removeFriend(User $user, User $friend)
    {
        $friendship = $this->repository->findBetween($user, $friend);

        if (!$friendship) {
            return ['success' => false, 'message' => 'Friendship not found'];
        }

        $this->repository->delete($friendship);
        
        return ['success' => true, 'message' => 'Friend removed'];
    }

    public function areFriends(User $user1, User $user2): bool
    {
        return $this->repository->areFriends($user1, $user2);
    }

    public function getFriendshipStatus(User $user, User $otherUser)
    {
        $friendship = $this->repository->findBetween($user, $otherUser);

        if (!$friendship) {
            return ['status' => 'none', 'friendship' => null];
        }

        return [
            'status' => $friendship->status->value,
            'is_sender' => $friendship->sender_id === $user->id,
            'friendship' => $friendship,
        ];
    }

    /**
     * Get detailed friendship status for profile display.
     */
    public function getProfileFriendshipStatus(User $currentUser, User $profileUser): array
    {
        if ($currentUser->id === $profileUser->id) {
            return ['status' => 'self', 'friendship' => null];
        }

        $result = $this->getFriendshipStatus($currentUser, $profileUser);

        if ($result['status'] === 'none') {
            return ['status' => 'none', 'friendship' => null];
        }

        if ($result['status'] === 'accepted') {
            return ['status' => 'friends', 'friendship' => $result['friendship']];
        }

        if ($result['status'] === 'pending') {
            if ($result['is_sender']) {
                return ['status' => 'pending_sent', 'friendship' => $result['friendship']];
            }
            return ['status' => 'pending_received', 'friendship' => $result['friendship']];
        }

        return ['status' => 'none', 'friendship' => null];
    }
}
