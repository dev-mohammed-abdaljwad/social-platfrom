<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\Follow\FollowService;
use App\Services\User\UserService;
use App\Transformers\Follow\FollowTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function __construct(
        protected FollowService $followService,
        protected UserService $userService,
    ) {}

    /**
     * POST /users/{userId}/follow
     * Follow a user (or send a follow request if their account is private).
     */
    public function follow(Request $request, int $userId): JsonResponse
    {
        $followee = $this->userService->find($userId);
        $result   = $this->followService->follow($request->user(), $followee);

        if (!$result['success']) {
            return response()->json(['message' => $result['message']], 422);
        }

        return response()->json([
            'message' => $result['message'],
            'data'    => new FollowTransformer($result['follow']->load(['follower', 'followee'])),
        ], 201);
    }

    /**
     * DELETE /users/{userId}/follow
     * Unfollow a user (also works for cancelling an accepted follow).
     */
    public function unfollow(Request $request, int $userId): JsonResponse
    {
        $followee = $this->userService->find($userId);
        $result   = $this->followService->unfollow($request->user(), $followee);

        if (!$result['success']) {
            return response()->json(['message' => $result['message']], 422);
        }

        return response()->json(['message' => $result['message']]);
    }

    /**
     * DELETE /follow-requests/{userId}/cancel
     * Cancel a pending outgoing follow request.
     */
    public function cancelRequest(Request $request, int $userId): JsonResponse
    {
        $followee = $this->userService->find($userId);
        $result   = $this->followService->cancelRequest($request->user(), $followee);

        if (!$result['success']) {
            return response()->json(['message' => $result['message']], 422);
        }

        return response()->json(['message' => $result['message']]);
    }

    /**
     * POST /follow-requests/{userId}/accept
     * Accept an incoming follow request (authenticated user is the followee).
     */
    public function acceptRequest(Request $request, int $userId): JsonResponse
    {
        $follower = $this->userService->find($userId);
        $result   = $this->followService->acceptRequest($request->user(), $follower);

        if (!$result['success']) {
            return response()->json(['message' => $result['message']], 422);
        }

        return response()->json([
            'message' => $result['message'],
            'data'    => new FollowTransformer($result['follow']->load(['follower', 'followee'])),
        ]);
    }

    /**
     * DELETE /follow-requests/{userId}/decline
     * Decline an incoming follow request (authenticated user is the followee).
     */
    public function declineRequest(Request $request, int $userId): JsonResponse
    {
        $follower = $this->userService->find($userId);
        $result   = $this->followService->declineRequest($request->user(), $follower);

        if (!$result['success']) {
            return response()->json(['message' => $result['message']], 422);
        }

        return response()->json(['message' => $result['message']]);
    }

    /**
     * GET /users/{userId}/followers
     * List accepted followers of a user.
     */
    public function followers(int $userId): JsonResponse
    {
        $this->userService->find($userId); // 404 if user not found

        $followers = $this->followService->getFollowers($userId);

        return response()->json([
            'data' => FollowTransformer::collection($followers),
            'meta' => [
                'current_page' => $followers->currentPage(),
                'last_page'    => $followers->lastPage(),
                'per_page'     => $followers->perPage(),
                'total'        => $followers->total(),
            ],
        ]);
    }

    /**
     * GET /users/{userId}/following
     * List accounts that a user is following (accepted).
     */
    public function following(int $userId): JsonResponse
    {
        $this->userService->find($userId); // 404 if user not found

        $following = $this->followService->getFollowing($userId);

        return response()->json([
            'data' => FollowTransformer::collection($following),
            'meta' => [
                'current_page' => $following->currentPage(),
                'last_page'    => $following->lastPage(),
                'per_page'     => $following->perPage(),
                'total'        => $following->total(),
            ],
        ]);
    }

    /**
     * GET /follow-requests
     * List all incoming pending follow requests for the authenticated user.
     */
    public function followRequests(Request $request): JsonResponse
    {
        $requests = $this->followService->getFollowRequests($request->user()->id);

        return response()->json([
            'data' => FollowTransformer::collection($requests),
            'meta' => [
                'current_page' => $requests->currentPage(),
                'last_page'    => $requests->lastPage(),
                'per_page'     => $requests->perPage(),
                'total'        => $requests->total(),
            ],
        ]);
    }

    /**
     * GET /users/{userId}/follow-status
     * Get the follow relationship status between the authenticated user and another user.
     */
    public function status(Request $request, int $userId): JsonResponse
    {
        $targetUser = $this->userService->find($userId);
        $status     = $this->followService->getStatus($request->user(), $targetUser);

        return response()->json(['data' => $status]);
    }
}
