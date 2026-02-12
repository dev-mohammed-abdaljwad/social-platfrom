<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Friendship\FriendshipService;
use App\Services\User\UserService;
use App\Transformers\Friendship\FriendshipTransformer;
use App\Transformers\User\UserTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Friendships
 *
 * APIs for managing friend requests and friendships
 */
class FriendshipController extends Controller
{
    public function __construct(
        protected FriendshipService $friendshipService,
        protected UserService $userService
    ) {}

    /**
     * Get friends list
     *
     * Get the authenticated user's friends.
     *
     * @response {
     *   "data": [
     *     {
     *       "id": 2,
     *       "name": "Jane Doe",
     *       "username": "janedoe",
     *       "profile_picture": null
     *     }
     *   ]
     * }
     */
    public function friends(Request $request): JsonResponse
    {
        $friends = $this->friendshipService->getFriendsOf($request->user());

        return response()->json([
            'data' => UserTransformer::collection($friends),
        ]);
    }

    /**
     * Get pending requests
     *
     * Get friend requests received by the authenticated user.
     *
     * @response {
     *   "data": [
     *     {
     *       "id": 1,
     *       "status": "pending",
     *       "sender": {"id": 2, "name": "Jane Doe"},
     *       "created_at": "2026-02-12T10:00:00.000000Z"
     *     }
     *   ]
     * }
     */
    public function pendingRequests(Request $request): JsonResponse
    {
        $requests = $this->friendshipService->getPendingRequestsFor($request->user());

        return response()->json([
            'data' => FriendshipTransformer::collection($requests->load('sender')),
        ]);
    }

    /**
     * Get sent requests
     *
     * Get friend requests sent by the authenticated user.
     *
     * @response {
     *   "data": [
     *     {
     *       "id": 1,
     *       "status": "pending",
     *       "receiver": {"id": 3, "name": "Bob Smith"},
     *       "created_at": "2026-02-12T10:00:00.000000Z"
     *     }
     *   ]
     * }
     */
    public function sentRequests(Request $request): JsonResponse
    {
        $requests = $this->friendshipService->getSentRequestsBy($request->user());

        return response()->json([
            'data' => FriendshipTransformer::collection($requests->load('receiver')),
        ]);
    }

    /**
     * Send friend request
     *
     * Send a friend request to another user.
     *
     * @urlParam userId integer required The target user's ID. Example: 2
     *
     * @response 201 {
     *   "message": "Friend request sent successfully",
     *   "data": {
     *     "id": 1,
     *     "status": "pending",
     *     "sender": {"id": 1, "name": "John Doe"},
     *     "receiver": {"id": 2, "name": "Jane Doe"}
     *   }
     * }
     * @response 400 {
     *   "message": "Friend request already exists"
     * }
     */
    public function sendRequest(Request $request, int $userId): JsonResponse
    {
        $receiver = $this->userService->find($userId);
        $result = $this->friendshipService->sendRequest($request->user(), $receiver);

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message'],
            ], 400);
        }

        return response()->json([
            'message' => $result['message'],
            'data' => new FriendshipTransformer($result['friendship']->load(['sender', 'receiver'])),
        ], 201);
    }

    /**
     * Accept friend request
     *
     * Accept a pending friend request.
     *
     * @urlParam friendshipId integer required The friendship request ID. Example: 1
     *
     * @response {
     *   "message": "Friend request accepted",
     *   "data": {
     *     "id": 1,
     *     "status": "accepted",
     *     "sender": {"id": 2, "name": "Jane Doe"},
     *     "receiver": {"id": 1, "name": "John Doe"}
     *   }
     * }
     * @response 400 {
     *   "message": "Cannot accept this request"
     * }
     */
    public function acceptRequest(Request $request, int $friendshipId): JsonResponse
    {
        $result = $this->friendshipService->acceptRequest($friendshipId, $request->user());

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message'],
            ], 400);
        }

        return response()->json([
            'message' => $result['message'],
            'data' => new FriendshipTransformer($result['friendship']->load(['sender', 'receiver'])),
        ]);
    }

    /**
     * Reject friend request
     *
     * Reject a pending friend request.
     *
     * @urlParam friendshipId integer required The friendship request ID. Example: 1
     *
     * @response {
     *   "message": "Friend request rejected"
     * }
     * @response 400 {
     *   "message": "Cannot reject this request"
     * }
     */
    public function rejectRequest(Request $request, int $friendshipId): JsonResponse
    {
        $result = $this->friendshipService->rejectRequest($friendshipId, $request->user());

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message'],
            ], 400);
        }

        return response()->json([
            'message' => $result['message'],
        ]);
    }

    /**
     * Remove friend
     *
     * Remove an existing friend.
     *
     * @urlParam userId integer required The friend's user ID. Example: 2
     *
     * @response {
     *   "message": "Friend removed successfully"
     * }
     * @response 400 {
     *   "message": "User is not your friend"
     * }
     */
    public function removeFriend(Request $request, int $userId): JsonResponse
    {
        $friend = $this->userService->find($userId);
        $result = $this->friendshipService->removeFriend($request->user(), $friend);

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message'],
            ], 400);
        }

        return response()->json([
            'message' => $result['message'],
        ]);
    }

    /**
     * Get friendship status
     *
     * Get the friendship status between the authenticated user and another user.
     *
     * @urlParam userId integer required The other user's ID. Example: 2
     *
     * @response {
     *   "data": {
     *     "status": "friends",
     *     "friendship_id": 1
     *   }
     * }
     * @response {
     *   "data": {
     *     "status": "not_friends",
     *     "friendship_id": null
     *   }
     * }
     */
    public function status(Request $request, int $userId): JsonResponse
    {
        $otherUser = $this->userService->find($userId);
        $status = $this->friendshipService->getFriendshipStatus($request->user(), $otherUser);

        return response()->json([
            'data' => $status,
        ]);
    }
}
