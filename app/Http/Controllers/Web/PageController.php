<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Follow\FollowService;
use App\Services\Friendship\FriendshipService;
use App\Services\Post\PostService;
use App\Services\User\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function __construct(
        protected PostService $postService,
        protected UserService $userService,
        protected FriendshipService $friendshipService,
        protected FollowService $followService,
        protected \App\Services\Feed\FeedService $feedService
    ) {}

    /**
     * Home page with posts feed.
     */
    public function home()
    {
        if (auth()->check()) {
            $posts = $this->feedService->getSmartFeed(auth()->user(), 1, 10);
        } else {
            $posts = $this->postService->getPublicPosts(10);
        }

        return view('home', compact('posts'));
    }

    /**
     * Fetch posts for infinite scroll.
     */
    public function fetchPosts(Request $request): JsonResponse
    {
        $limit = (int) $request->query('limit', 10);
        $page = (int) $request->query('page', 1);

        if (auth()->check()) {
            // Using Smart Feed which uses page numbers, not last_id cursor
            $posts = $this->feedService->getSmartFeed(auth()->user(), $page, $limit);
            $hasMore = $posts->count() === $limit;
            $nextPage = $page + 1;
        } else {
            $lastId = $request->query('last_id');
            $posts = $this->postService->getPublicPosts($limit, $lastId);
            $hasMore = $posts->count() === $limit;
            $nextPage = null;
        }

        $postsHtml = '';
        foreach ($posts as $post) {
            $postsHtml .= view('components.post-card', compact('post'))->render();
        }

        return response()->json([
            'success' => true,
            'html' => $postsHtml,
            'has_more' => $hasMore,
            'last_id' => $posts->last()?->id,
            'next_page' => $nextPage,
        ]);
    }

    /**
     * Current user's profile.
     */
    public function profile()
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $posts          = $this->postService->getUserPostsWithRelations($user);
        $sharedPosts    = $this->postService->getUserSharedPosts($user);
        $friends        = $this->friendshipService->getFriendsOf($user);
        $followersCount = $this->followService->getFollowers($user->id)->total();
        $followingCount = $this->followService->getFollowing($user->id)->total();

        return view('profile', [
            'user'           => $user,
            'posts'          => $posts,
            'sharedPosts'    => $sharedPosts,
            'friends'        => $friends,
            'isOwnProfile'   => true,
            'followersCount' => $followersCount,
            'followingCount' => $followingCount,
        ]);
    }

    /**
     * Show a specific user's profile.
     */
    public function showProfile(int $userId)
    {
        $user         = $this->userService->find($userId);
        $isOwnProfile = auth()->check() && auth()->id() === $user->id;

        $posts       = $this->postService->getUserPostsWithRelations($user, !$isOwnProfile);
        $sharedPosts = $this->postService->getUserSharedPosts($user);
        $friends     = $this->friendshipService->getFriendsOf($user);

        // Follower / following counts
        $followersCount = $this->followService->getFollowers($user->id)->total();
        $followingCount = $this->followService->getFollowing($user->id)->total();

        // Friendship status for non-own profiles
        $friendshipStatus = null;
        $friendship       = null;

        // Follow status for non-own profiles
        $followStatus = 'none';

        if (auth()->check() && !$isOwnProfile) {
            $currentUser = auth()->user();

            $result           = $this->friendshipService->getProfileFriendshipStatus($currentUser, $user);
            $friendshipStatus = $result['status'];
            $friendship       = $result['friendship'];

            $followData   = $this->followService->getStatus($currentUser, $user);
            $followStatus = $followData['status']; // none | pending | accepted
        }

        return view('profile', [
            'user'             => $user,
            'posts'            => $posts,
            'sharedPosts'      => $sharedPosts,
            'friends'          => $friends,
            'isOwnProfile'     => $isOwnProfile,
            'friendshipStatus' => $friendshipStatus,
            'friendship'       => $friendship,
            'followStatus'     => $followStatus,
            'followersCount'   => $followersCount,
            'followingCount'   => $followingCount,
        ]);
    }

    /**
     * Friends page.
     */
    public function friends()
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $friends = $this->friendshipService->getFriendsOf($user);
        $pendingRequests = $this->friendshipService->getPendingRequestsFor($user);
        $sentRequests = $this->friendshipService->getSentRequestsBy($user);

        // Suggestions: users who are not friends and not in pending requests
        $friendIds = $friends->pluck('id')->toArray();
        $pendingIds = $pendingRequests->pluck('sender_id')->toArray();
        $sentIds = $sentRequests->pluck('receiver_id')->toArray();
        $excludeIds = array_merge($friendIds, $pendingIds, $sentIds, [$user->id]);

        $suggestions = $this->userService->getSuggestions($user, $excludeIds, 6);

        return view('friends', [
            'friends' => $friends,
            'pendingRequests' => $pendingRequests,
            'sentRequests' => $sentRequests,
            'suggestions' => $suggestions,
        ]);
    }

    /**
     * Settings page.
     */
    public function settings()
    {
        return view('settings');
    }

    /**
     * Explore page.
     */
    public function explore()
    {
        return view('explore');
    }

    /**
     * Saved posts page.
     */
    public function saved()
    {
        $user = auth()->user();

        $savedPosts = $this->postService->getSavedPostsForUser($user);

        return view('saved', compact('savedPosts'));
    }
}
