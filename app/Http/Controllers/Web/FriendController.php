<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Friendship\FriendshipService;
use App\Services\Notification\NotificationService;
use App\Services\User\UserService;

class FriendController extends Controller
{
    public function __construct(
        protected FriendshipService $friendshipService,
        protected UserService $userService,
        protected NotificationService $notificationService
    ) {}

    /**
     * Send a friend request.
     */
    public function send(int $userId)
    {
        $currentUser = auth()->user();
        $receiver = $this->userService->find($userId);

        $result = $this->friendshipService->sendRequest($currentUser, $receiver);

        if (!$result['success']) {
            return response()->json(['success' => false, 'message' => $result['message']], 400);
        }

        // Send notification
        $this->notificationService->friendRequest($receiver, $currentUser, $result['friendship']);

        return response()->json(['success' => true, 'message' => $result['message']]);
    }

    /**
     * Accept a friend request.
     */
    public function accept(int $friendshipId)
    {
        $currentUser = auth()->user();
        $result = $this->friendshipService->acceptRequest($friendshipId, $currentUser);

        if (!$result['success']) {
            $statusCode = $result['message'] === 'Unauthorized to accept this request' ? 403 : 400;
            return response()->json(['success' => false, 'message' => $result['message']], $statusCode);
        }

        // Send notification to the sender
        $friendship = $result['friendship'];
        $this->notificationService->friendAccepted(
            $friendship->sender,
            $currentUser,
            $friendship
        );

        return response()->json(['success' => true, 'message' => $result['message']]);
    }

    /**
     * Reject a friend request.
     */
    public function reject(int $friendshipId)
    {
        $currentUser = auth()->user();
        $result = $this->friendshipService->rejectRequest($friendshipId, $currentUser);

        if (!$result['success']) {
            $statusCode = $result['message'] === 'Unauthorized to reject this request' ? 403 : 400;
            return response()->json(['success' => false, 'message' => $result['message']], $statusCode);
        }

        return response()->json(['success' => true, 'message' => $result['message']]);
    }

    /**
     * Cancel a sent friend request.
     */
    public function cancel(int $friendshipId)
    {
        $currentUser = auth()->user();
        $result = $this->friendshipService->cancelRequest($friendshipId, $currentUser);

        if (!$result['success']) {
            $statusCode = $result['message'] === 'Unauthorized to cancel this request' ? 403 : 400;
            return response()->json(['success' => false, 'message' => $result['message']], $statusCode);
        }

        return response()->json(['success' => true, 'message' => $result['message']]);
    }

    /**
     * Remove a friend.
     */
    public function remove(int $userId)
    {
        $currentUser = auth()->user();
        $friend = $this->userService->find($userId);

        $result = $this->friendshipService->removeFriend($currentUser, $friend);

        if (!$result['success']) {
            return response()->json(['success' => false, 'message' => $result['message']], 404);
        }

        return response()->json(['success' => true, 'message' => $result['message']]);
    }
}
