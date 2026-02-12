<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\User;

class PageController extends Controller
{
    /**
     * Home page with posts feed.
     */
    public function home()
    {
        $posts = Post::with(['user', 'comments', 'likes'])
            ->where('privacy', 'public')
            ->latest()
            ->paginate(10);

        return view('home', compact('posts'));
    }

    /**
     * Current user's profile.
     */
    public function profile()
    {
        $user = auth()->user() ?? User::first();

        return view('profile', [
            'user' => $user,
            'isOwnProfile' => true,
        ]);
    }

    /**
     * Show a specific user's profile.
     */
    public function showProfile(User $user)
    {
        return view('profile', [
            'user' => $user,
            'isOwnProfile' => auth()->check() && auth()->id() === $user->id,
        ]);
    }

    /**
     * Friends page.
     */
    public function friends()
    {
        return view('friends');
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
