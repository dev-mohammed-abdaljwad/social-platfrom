<?php

namespace App\Repositories\Friendship;

use App\Models\User;

interface FriendshipRepository
{
    public function all();
    public function find($id);
    public function findBetween(User $user1, User $user2);
    public function getPendingRequestsFor(User $user);
    public function getSentRequestsBy(User $user);
    public function getFriendsOf(User $user);
    public function create(array $data);
    public function update($model, array $data);
    public function delete($model);
    public function sendRequest(User $sender, User $receiver);
    public function acceptRequest($friendship);
    public function rejectRequest($friendship);
    public function areFriends(User $user1, User $user2): bool;
}
