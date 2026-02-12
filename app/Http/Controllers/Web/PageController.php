<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\User;
use App\Models\Share;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * Home page with posts feed.
     */
    public function home()
    {
        $posts = Post::with(['user', 'comments', 'likes', 'shares'])
            ->where('privacy', 'public')
            ->orderBy('id', 'desc')
            ->take(10)
            ->get();

        return view('home', compact('posts'));
    }

    /**
     * Fetch posts for infinite scroll.
     */
    public function fetchPosts(Request $request): JsonResponse
    {
        $lastId = $request->query('last_id');
        $limit = (int) $request->query('limit', 10);
        
        $query = Post::with(['user', 'comments', 'likes', 'shares'])
            ->where('privacy', 'public')
            ->orderBy('id', 'desc');
        
        if ($lastId) {
            $query->where('id', '<', $lastId);
        }
        
        $posts = $query->take($limit)->get();
        
        $postsHtml = '';
        foreach ($posts as $post) {
            $postsHtml .= view('partials.post-card', compact('post'))->render();
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

        $posts = $user->posts()
            ->with(['user', 'comments', 'likes', 'shares'])
            ->latest()
            ->get();

        $sharedPosts = $user->shares()
            ->with(['post.user', 'post.comments', 'post.likes', 'post.shares'])
            ->latest()
            ->get();

        $friends = $user->friends()->get();

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
    public function showProfile(User $user)
    {
        $posts = $user->posts()
            ->with(['user', 'comments', 'likes', 'shares'])
            ->where('privacy', 'public')
            ->latest()
            ->get();

        $sharedPosts = $user->shares()
            ->with(['post.user', 'post.comments', 'post.likes', 'post.shares'])
            ->latest()
            ->get();

        $friends = $user->friends()->get();

        return view('profile', [
            'user' => $user,
            'posts' => $posts,
            'sharedPosts' => $sharedPosts,
            'friends' => $friends,
            'isOwnProfile' => auth()->check() && auth()->id() === $user->id,
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

        $friends = $user->friends()->get();
        $pendingRequests = $user->pendingFriendRequests()->with('sender')->get();
        $sentRequests = $user->sentFriendRequests()
            ->where('status', \App\Enums\FriendshipStatusEnum::Pending)
            ->with('receiver')
            ->get();
        
        // Suggestions: users who are not friends and not in pending requests
        $friendIds = $friends->pluck('id')->toArray();
        $pendingIds = $pendingRequests->pluck('sender_id')->toArray();
        $sentIds = $sentRequests->pluck('receiver_id')->toArray();
        $excludeIds = array_merge($friendIds, $pendingIds, $sentIds, [$user->id]);
        
        $suggestions = User::whereNotIn('id', $excludeIds)
            ->inRandomOrder()
            ->limit(6)
            ->get();

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
}
