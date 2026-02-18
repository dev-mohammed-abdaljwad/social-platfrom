<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
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
        protected FriendshipService $friendshipService
    ) {}

    /**
     * Home page with posts feed.
     */
    public function home()
    {
        $posts = $this->postService->getPublicPosts(10);

        return view('home', compact('posts'));
    }

    /**
     * Fetch posts for infinite scroll.
     */
    public function fetchPosts(Request $request): JsonResponse
    {
        $lastId = $request->query('last_id');
        $limit = (int) $request->query('limit', 10);

        $posts = $this->postService->getPublicPosts($limit, $lastId);

        $postsHtml = '';
        foreach ($posts as $post) {
            $postsHtml .= view('components.post-card', compact('post'))->render();
        }

        return response()->json([
            'success' => true,
            'html' => $postsHtml,
            'has_more' => $posts->count() === $limit,
            'last_id' => $posts->last()?->id,
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

        $posts = $this->postService->getUserPostsWithRelations($user);
        $sharedPosts = $this->postService->getUserSharedPosts($user);
        $friends = $this->friendshipService->getFriendsOf($user);

        return view('profile', [
            'user' => $user,
            'posts' => $posts,
            'sharedPosts' => $sharedPosts,
            'friends' => $friends,
            'isOwnProfile' => true,
        ]);
    }

    /**
     * Show a specific user's profile.
     */
    public function showProfile(int $userId)
    {
        $user = $this->userService->find($userId);
        $isOwnProfile = auth()->check() && auth()->id() === $user->id;

        $posts = $this->postService->getUserPostsWithRelations($user, !$isOwnProfile);
        $sharedPosts = $this->postService->getUserSharedPosts($user);
        $friends = $this->friendshipService->getFriendsOf($user);

        // Friendship status for non-own profiles
        $friendshipStatus = null;
        $friendship = null;

        if (auth()->check() && !$isOwnProfile) {
            $currentUser = auth()->user();
            $result = $this->friendshipService->getProfileFriendshipStatus($currentUser, $user);
            $friendshipStatus = $result['status'];
            $friendship = $result['friendship'];
        }

        
        return view('profile', [
            'user' => $user,
            'posts' => $posts,
            'sharedPosts' => $sharedPosts,
            'friends' => $friends,
            'isOwnProfile' => $isOwnProfile,
            'friendshipStatus' => $friendshipStatus,
            'friendship' => $friendship,
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
