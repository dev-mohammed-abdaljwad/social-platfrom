<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Follow\FollowService;
use App\Services\User\UserService;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function __construct(
        protected FollowService $followService,
        protected UserService   $userService,
    ) {}

    // -------------------------------------------------------------------------
    // Pages
    // -------------------------------------------------------------------------

    /**
     * GET /follows
     * The authenticated user's followers & following hub.
     */
    public function index(Request $request)
    {
        $user      = auth()->user();
        $tab       = $request->query('tab', 'followers'); // followers | following | requests
        $followers = $this->followService->getFollowers($user->id);
        $following = $this->followService->getFollowing($user->id);
        $requests  = $this->followService->getFollowRequests($user->id);

        return view('follows', compact('user', 'tab', 'followers', 'following', 'requests'));
    }

    /**
     * GET /users/{userId}/followers
     * Public followers list for any user.
     */
    public function userFollowers(int $userId)
    {
        $profileUser = $this->userService->find($userId);
        $followers   = $this->followService->getFollowers($userId);

        return view('follows', [
            'user'      => $profileUser,
            'tab'       => 'followers',
            'followers' => $followers,
            'following' => collect(),
            'requests'  => collect(),
        ]);
    }

    /**
     * GET /users/{userId}/following
     * Public following list for any user.
     */
    public function userFollowing(int $userId)
    {
        $profileUser = $this->userService->find($userId);
        $following   = $this->followService->getFollowing($userId);

        return view('follows', [
            'user'      => $profileUser,
            'tab'       => 'following',
            'followers' => collect(),
            'following' => $following,
            'requests'  => collect(),
        ]);
    }

    // -------------------------------------------------------------------------
    // Follow actions (AJAX-friendly, JSON + redirect fallback)
    // -------------------------------------------------------------------------

    /**
     * POST /follow/{userId}
     * Follow a user (or send a pending request for private accounts).
     */
    public function follow(int $userId)
    {
        $followee = $this->userService->find($userId);
        $result   = $this->followService->follow(auth()->user(), $followee);

        if (request()->expectsJson()) {
            $status = $result['success'] ? 200 : 422;
            return response()->json($result, $status);
        }

        $flash = $result['success'] ? 'success' : 'error';
        return redirect()->back()->with($flash, $result['message']);
    }

    /**
     * DELETE /follow/{userId}
     * Unfollow a user.
     */
    public function unfollow(int $userId)
    {
        $followee = $this->userService->find($userId);
        $result   = $this->followService->unfollow(auth()->user(), $followee);

        if (request()->expectsJson()) {
            $status = $result['success'] ? 200 : 422;
            return response()->json($result, $status);
        }

        $flash = $result['success'] ? 'success' : 'error';
        return redirect()->back()->with($flash, $result['message']);
    }

    /**
     * DELETE /follow-requests/{userId}/cancel
     * Cancel an outgoing pending follow request.
     */
    public function cancelRequest(int $userId)
    {
        $followee = $this->userService->find($userId);
        $result   = $this->followService->cancelRequest(auth()->user(), $followee);

        if (request()->expectsJson()) {
            $status = $result['success'] ? 200 : 422;
            return response()->json($result, $status);
        }

        $flash = $result['success'] ? 'success' : 'error';
        return redirect()->back()->with($flash, $result['message']);
    }

    /**
     * POST /follow-requests/{userId}/accept
     * Accept an incoming follow request (auth user is the followee).
     */
    public function acceptRequest(int $userId)
    {
        $follower = $this->userService->find($userId);
        $result   = $this->followService->acceptRequest(auth()->user(), $follower);

        if (request()->expectsJson()) {
            $status = $result['success'] ? 200 : 422;
            return response()->json($result, $status);
        }

        $flash = $result['success'] ? 'success' : 'error';
        return redirect()->back()->with($flash, $result['message']);
    }

    /**
     * DELETE /follow-requests/{userId}/decline
     * Decline an incoming follow request.
     */
    public function declineRequest(int $userId)
    {
        $follower = $this->userService->find($userId);
        $result   = $this->followService->declineRequest(auth()->user(), $follower);

        if (request()->expectsJson()) {
            $status = $result['success'] ? 200 : 422;
            return response()->json($result, $status);
        }

        $flash = $result['success'] ? 'success' : 'error';
        return redirect()->back()->with($flash, $result['message']);
    }

    /**
     * GET /follow-status/{userId}
     * JSON endpoint — returns follow status between auth user and target user.
     */
    public function status(int $userId)
    {
        $targetUser = $this->userService->find($userId);
        $status     = $this->followService->getStatus(auth()->user(), $targetUser);

        return response()->json(['data' => $status]);
    }
}
