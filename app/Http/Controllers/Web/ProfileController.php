<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Profile\DeleteAccountRequest;
use App\Http\Requests\Web\Profile\UpdateCoverPhotoRequest;
use App\Http\Requests\Web\Profile\UpdateEmailRequest;
use App\Http\Requests\Web\Profile\UpdatePasswordRequest;
use App\Http\Requests\Web\Profile\UpdateProfilePictureRequest;
use App\Http\Requests\Web\Profile\UpdateProfileRequest;
use App\Services\User\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    /**
     * Update the user's profile information.
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $updatedUser = $this->userService->updateProfileInfo($user, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'user' => $updatedUser,
        ]);
    }

    /**
     * Update the user's email address.
     */
    public function updateEmail(UpdateEmailRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $result = $this->userService->updateEmail(
            $user,
            $validated['email'],
            $validated['password']
        );

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
        ]);
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $result = $this->userService->updatePassword(
            $user,
            $validated['current_password'],
            $validated['password']
        );

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
        ]);
    }

    /**
     * Delete the user's account.
     */
    public function deleteAccount(DeleteAccountRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $result = $this->userService->deleteAccount($user, $validated['password']);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 422);
        }

        // Logout from web session
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'redirect' => '/',
        ]);
    }

    /**
     * Update the user's profile picture.
     */
    public function updateProfilePicture(UpdateProfilePictureRequest $request): JsonResponse
    {
        $user = $request->user();
        $result = $this->userService->updateProfilePicture($user, $request->file('profile_picture'));

        return response()->json($result);
    }

    /**
     * Update the user's cover photo.
     */
    public function updateCoverPhoto(UpdateCoverPhotoRequest $request): JsonResponse
    {
        $user = $request->user();
        $result = $this->userService->updateCoverPhoto($user, $request->file('cover_photo'));

        return response()->json($result);
    }

    /**
     * Remove the user's profile picture.
     */
    public function removeProfilePicture(Request $request): JsonResponse
    {
        $user = $request->user();
        $result = $this->userService->removeProfilePicture($user);

        return response()->json($result);
    }

    /**
     * Remove the user's cover photo.
     */
    public function removeCoverPhoto(Request $request): JsonResponse
    {
        $user = $request->user();
        $result = $this->userService->removeCoverPhoto($user);

        return response()->json($result);
    }
}
