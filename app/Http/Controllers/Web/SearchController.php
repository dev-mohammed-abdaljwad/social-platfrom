<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Friendship\FriendshipService;
use App\Services\User\UserService;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __construct(
        protected UserService $userService,
        protected FriendshipService $friendshipService
    ) {}

    /**
     * Display search page with results.
     */
    public function index(Request $request)
    {
        $query = $request->get('q', '');
        $users = collect();

        if (strlen($query) >= 2) {
            $currentUserId = auth()->id();
            $users = $this->userService->searchPaginated($query, 15, $currentUserId);

            // Add friendship status for each user
            if (auth()->check()) {
                $currentUser = auth()->user();
                $users->getCollection()->transform(function ($user) use ($currentUser) {
                    $user->friendship_status = $this->friendshipService->getFriendshipStatus($currentUser, $user);
                    return $user;
                });
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'users' => $users->map(fn($user) => $this->formatUser($user)),
                'query' => $query,
            ]);
        }

        return view('search', [
            'users' => $users,
            'query' => $query,
        ]);
    }

    /**
     * AJAX search for autocomplete/live search.
     */
    public function suggestions(Request $request)
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json(['users' => []]);
        }

        $currentUserId = auth()->id();
        $users = $this->userService->search($query, 5);

        // Filter out current user
        if ($currentUserId) {
            $users = $users->filter(fn($user) => $user->id !== $currentUserId);
        }

        // Add friendship status if authenticated
        if (auth()->check()) {
            $currentUser = auth()->user();
            $users = $users->map(function ($user) use ($currentUser) {
                $user->friendship_status = $this->friendshipService->getFriendshipStatus($currentUser, $user);
                return $user;
            });
        }

        return response()->json([
            'users' => $users->values()->map(fn($user) => $this->formatUser($user)),
        ]);
    }

    /**
     * Format user for JSON response.
     */
    protected function formatUser($user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'avatar_url' => $user->avatar_url,
            'bio' => $user->bio,
            'profile_url' => route('profile.show', $user),
            'friendship_status' => $user->friendship_status ?? null,
        ];
    }
}
