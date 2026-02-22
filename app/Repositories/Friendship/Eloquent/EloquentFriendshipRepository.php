<?php

namespace App\Repositories\Friendship\Eloquent;

use App\Enums\FriendshipStatusEnum;
use App\Models\Friendship;
use App\Models\User;
use App\Repositories\Friendship\FriendshipRepository;

class EloquentFriendshipRepository implements FriendshipRepository
{
    public function __construct(protected Friendship $model) {}

    public function all()
    {
        return $this->model->query()->with(['sender', 'receiver'])->latest()->get();
    }

    public function find($id)
    {
        return $this->model->with(['sender', 'receiver'])->findOrFail($id);
    }

    public function findBetween(User $user1, User $user2)
    {
        return $this->model->where(function ($query) use ($user1, $user2) {
            $query->where('sender_id', $user1->id)
                ->where('receiver_id', $user2->id);
        })->orWhere(function ($query) use ($user1, $user2) {
            $query->where('sender_id', $user2->id)
                ->where('receiver_id', $user1->id);
        })->first();
    }

    public function getPendingRequestsFor(User $user)
    {
        return $this->model->where('receiver_id', $user->id)
            ->where('status', FriendshipStatusEnum::Pending)
            ->with('sender')
            ->latest()
            ->get();
    }

    public function getSentRequestsBy(User $user)
    {
        return $this->model->where('sender_id', $user->id)
            ->where('status', FriendshipStatusEnum::Pending)
            ->with('receiver')
            ->latest()
            ->get();
    }

    public function getFriendsOf(User $user)
    {
        // Single query to get all accepted friendships where user is sender OR receiver
        $friendIds = $this->model
            ->where('status', FriendshipStatusEnum::Accepted)
            ->where(function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                    ->orWhere('receiver_id', $user->id);
            })
            ->get()
            ->map(function ($friendship) use ($user) {
                return $friendship->sender_id === $user->id
                    ? $friendship->receiver_id
                    : $friendship->sender_id;
            });

        return User::whereIn('id', $friendIds)->get();
    }

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

    public function sendRequest(User $sender, User $receiver)
    {
        // Check if friendship already exists
        $existing = $this->findBetween($sender, $receiver);

        if ($existing) {
            return ['success' => false, 'message' => 'Friendship request already exists', 'friendship' => $existing];
        }

        $friendship = $this->create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'status' => FriendshipStatusEnum::Pending->value,
        ]);

        return ['success' => true, 'message' => 'Friend request sent', 'friendship' => $friendship];
    }

    public function acceptRequest($friendship)
    {
        return $this->update($friendship, ['status' => FriendshipStatusEnum::Accepted->value]);
    }

    public function rejectRequest($friendship)
    {
        return $this->delete($friendship);
    }

    public function areFriends(User $user1, User $user2): bool
    {
        return $this->model->where(function ($query) use ($user1, $user2) {
            $query->where('sender_id', $user1->id)
                ->where('receiver_id', $user2->id);
        })->orWhere(function ($query) use ($user1, $user2) {
            $query->where('sender_id', $user2->id)
                ->where('receiver_id', $user1->id);
        })->where('status', FriendshipStatusEnum::Accepted)->exists();
    }
}
