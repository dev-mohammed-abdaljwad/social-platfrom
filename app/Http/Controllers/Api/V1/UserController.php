<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\User\UpdateProfileRequest;
use App\Services\User\UserService;
use App\Transformers\User\UserTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Users
 *
 * APIs for managing user profiles and discovery
 */
class UserController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    /**
     * List users
     *
     * Get all users or search by query.
     *
     * @queryParam q string Search query for name, username or email. Example: john
     *
     * @response {
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "John Doe",
     *       "username": "johndoe",
     *       "email": "john@example.com",
     *       "bio": "Software developer"
     *     }
     *   ]
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $query = $request->get('q');
        
        if ($query) {
            $users = $this->userService->search($query);
        } else {
            $users = $this->userService->all();
        }

        return response()->json([
            'data' => UserTransformer::collection($users),
        ]);
    }

    /**
     * Get user by ID
     *
     * Get a specific user's profile by their ID.
     *
     * @urlParam id integer required The user ID. Example: 1
     *
     * @response {
     *   "data": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "username": "johndoe",
     *     "email": "john@example.com",
     *     "bio": "Software developer",
     *     "profile_picture": null,
     *     "created_at": "2026-02-12T10:00:00.000000Z"
     *   }
     * }
     * @response 404 {
     *   "message": "User not found"
     * }
     */
    public function show(int $id): JsonResponse
    {
        $user = $this->userService->find($id);

        return response()->json([
            'data' => new UserTransformer($user),
        ]);
    }

    /**
     * Get user by username
     *
     * Get a specific user's profile by their username.
     *
     * @urlParam username string required The username. Example: johndoe
     *
     * @response {
     *   "data": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "username": "johndoe",
     *     "email": "john@example.com"
     *   }
     * }
     * @response 404 {
     *   "message": "User not found"
     * }
     */
    public function showByUsername(string $username): JsonResponse
    {
        $user = $this->userService->findByUsername($username);

        if (!$user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }

        return response()->json([
            'data' => new UserTransformer($user),
        ]);
    }

    /**
     * Update profile
     *
     * Update the authenticated user's profile information.
     *
     * @bodyParam name string The user's name. Example: John Updated
     * @bodyParam bio string The user's bio. Example: Updated bio
     *
     * @response {
     *   "message": "Profile updated successfully",
     *   "data": {
     *     "id": 1,
     *     "name": "John Updated",
     *     "bio": "Updated bio"
     *   }
     * }
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $this->userService->updateProfile(
            $request->user(),
            $request->validated()
        );

        return response()->json([
            'message' => 'Profile updated successfully',
            'data' => new UserTransformer($user),
        ]);
    }

    /**
     * Update profile picture
     *
     * Upload a new profile picture for the authenticated user.
     *
     * @bodyParam profile_picture file required The profile picture image (max 2MB). No-example
     *
     * @response {
     *   "message": "Profile picture updated successfully",
     *   "data": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "profile_picture": "profile-pictures/abc123.jpg"
     *   }
     * }
     * @response 422 {
     *   "message": "The profile picture must be an image."
     * }
     */
    public function updateProfilePicture(Request $request): JsonResponse
    {
        $request->validate([
            'profile_picture' => ['required', 'image', 'max:2048'],
        ]);

        $path = $request->file('profile_picture')->store('profile-pictures', 'public');

        $user = $this->userService->update($request->user(), [
            'profile_picture' => $path,
        ]);

        return response()->json([
            'message' => 'Profile picture updated successfully',
            'data' => new UserTransformer($user),
        ]);
    }
}
