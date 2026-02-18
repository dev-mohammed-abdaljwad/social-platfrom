<?php

namespace App\Http\Controllers\Web;

use App\Events\FriendRequestAccepted;
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
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => $result['message']], 400);
            }
            return redirect()->back()->with('error', $result['message']);
        }

        // Send notification
        $this->notificationService->friendRequest($receiver, $currentUser, $result['friendship']);

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => $result['message']]);
        }

        return redirect()->back()->with('success', $result['message']);
    }

    /**
     * Accept a friend request.
     */
    public function accept(int $friendshipId)
    {
        $currentUser = auth()->user();
        $result = $this->friendshipService->acceptRequest($friendshipId, $currentUser);

        if (!$result['success']) {
            if (request()->expectsJson()) {
                $statusCode = $result['message'] === 'Unauthorized to accept this request' ? 403 : 400;
                return response()->json(['success' => false, 'message' => $result['message']], $statusCode);
            }
            return redirect()->back()->with('error', $result['message']);
        }

        // Send notification to the sender
        $friendship = $result['friendship'];
        broadcast(new FriendRequestAccepted($friendship));
        $this->notificationService->friendAccepted(
            $friendship->sender,
            $currentUser,
            $friendship
        );

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => $result['message']]);
        }

        return redirect()->back()->with('success', $result['message']);
    }

    /**
     * Reject a friend request.
     */
    public function reject(int $friendshipId)
    {
        $currentUser = auth()->user();
        $result = $this->friendshipService->rejectRequest($friendshipId, $currentUser);

        if (!$result['success']) {
            if (request()->expectsJson()) {
                $statusCode = $result['message'] === 'Unauthorized to reject this request' ? 403 : 400;
                return response()->json(['success' => false, 'message' => $result['message']], $statusCode);
            }
            return redirect()->back()->with('error', $result['message']);
        }

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => $result['message']]);
        }

        return redirect()->back()->with('success', $result['message']);
    }

    /**
     * Cancel a sent friend request.
     */
    public function cancel(int $friendshipId)
    {
        $currentUser = auth()->user();
        $result = $this->friendshipService->cancelRequest($friendshipId, $currentUser);

        if (!$result['success']) {
            if (request()->expectsJson()) {
                $statusCode = $result['message'] === 'Unauthorized to cancel this request' ? 403 : 400;
                return response()->json(['success' => false, 'message' => $result['message']], $statusCode);
            }
            return redirect()->back()->with('error', $result['message']);
        }

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => $result['message']]);
        }

        return redirect()->back()->with('success', $result['message']);
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
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => $result['message']], 404);
            }
            return redirect()->back()->with('error', $result['message']);
        }

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => $result['message']]);
        }

        return redirect()->back()->with('success', $result['message']);
    }
}
