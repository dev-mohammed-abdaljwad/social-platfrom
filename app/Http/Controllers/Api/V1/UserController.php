<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\User\UpdateProfileRequest;
use App\Services\User\UserService;
use App\Transformers\User\UserTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

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

    public function show(int $id): JsonResponse
    {
        $user = $this->userService->find($id);

        return response()->json([
            'data' => new UserTransformer($user),
        ]);
    }

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
