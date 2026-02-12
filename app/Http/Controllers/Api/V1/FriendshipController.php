<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\Friendship\FriendshipService;
use App\Services\User\UserService;
use App\Transformers\Friendship\FriendshipTransformer;
use App\Transformers\User\UserTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FriendshipController extends Controller
{
    public function __construct(
        protected FriendshipService $friendshipService,
        protected UserService $userService
    ) {}

    public function friends(Request $request): JsonResponse
    {
        $friends = $this->friendshipService->getFriendsOf($request->user());

        return response()->json([
            'data' => UserTransformer::collection($friends),
        ]);
    }

    public function pendingRequests(Request $request): JsonResponse
    {
        $requests = $this->friendshipService->getPendingRequestsFor($request->user());

        return response()->json([
            'data' => FriendshipTransformer::collection($requests->load('sender')),
        ]);
    }

    public function sentRequests(Request $request): JsonResponse
    {
        $requests = $this->friendshipService->getSentRequestsBy($request->user());

        return response()->json([
            'data' => FriendshipTransformer::collection($requests->load('receiver')),
        ]);
    }

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

    public function status(Request $request, int $userId): JsonResponse
    {
        $otherUser = $this->userService->find($userId);
        $status = $this->friendshipService->getFriendshipStatus($request->user(), $otherUser);

        return response()->json([
            'data' => $status,
        ]);
    }
}
