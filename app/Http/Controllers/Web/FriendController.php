<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Friendship;
use App\Models\User;
use App\Enums\FriendshipStatusEnum;
use Illuminate\Http\Request;

class FriendController extends Controller
{
    /**
     * Send a friend request.
     */
    public function send(User $user)
    {
        $currentUser = auth()->user();

        // Can't send to yourself
        if ($currentUser->id === $user->id) {
            return response()->json(['success' => false, 'message' => 'Cannot send request to yourself'], 400);
        }

        // Check if request already exists
        $exists = Friendship::where(function ($query) use ($currentUser, $user) {
            $query->where('sender_id', $currentUser->id)->where('receiver_id', $user->id);
        })->orWhere(function ($query) use ($currentUser, $user) {
            $query->where('sender_id', $user->id)->where('receiver_id', $currentUser->id);
        })->exists();

        if ($exists) {
            return response()->json(['success' => false, 'message' => 'Request already exists'], 400);
        }

        Friendship::create([
            'sender_id' => $currentUser->id,
            'receiver_id' => $user->id,
            'status' => FriendshipStatusEnum::Pending,
        ]);

        return response()->json(['success' => true, 'message' => 'Friend request sent']);
    }

    /**
     * Accept a friend request.
     */
    public function accept(Friendship $friendship)
    {
        if ($friendship->receiver_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $friendship->update(['status' => FriendshipStatusEnum::Accepted]);

        return response()->json(['success' => true, 'message' => 'Friend request accepted']);
    }

    /**
     * Reject a friend request.
     */
    public function reject(Friendship $friendship)
    {
        if ($friendship->receiver_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $friendship->delete();

        return response()->json(['success' => true, 'message' => 'Friend request rejected']);
    }

    /**
     * Cancel a sent friend request.
     */
    public function cancel(Friendship $friendship)
    {
        if ($friendship->sender_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $friendship->delete();

        return response()->json(['success' => true, 'message' => 'Friend request cancelled']);
    }

    /**
     * Remove a friend.
     */
    public function remove(User $user)
    {
        $currentUser = auth()->user();

        $friendship = Friendship::where(function ($query) use ($currentUser, $user) {
            $query->where('sender_id', $currentUser->id)->where('receiver_id', $user->id);
        })->orWhere(function ($query) use ($currentUser, $user) {
            $query->where('sender_id', $user->id)->where('receiver_id', $currentUser->id);
        })->where('status', FriendshipStatusEnum::Accepted)->first();

        if (!$friendship) {
            return response()->json(['success' => false, 'message' => 'Not friends'], 404);
        }

        $friendship->delete();

        return response()->json(['success' => true, 'message' => 'Friend removed']);
    }
}
