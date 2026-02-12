<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Http\Requests\Api\V1\Auth\ChangePasswordRequest;
use App\Services\Auth\AuthService;
use App\Transformers\User\UserTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Authentication
 *
 * APIs for user authentication and token management
 */
class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * Register a new user
     *
     * Create a new user account and receive an access token.
     *
     * @unauthenticated
     *
     * @bodyParam name string required The user's full name. Example: John Doe
     * @bodyParam email string required The user's email address. Example: john@example.com
     * @bodyParam username string required The user's unique username. Example: johndoe
     * @bodyParam password string required The password (min 8 characters). Example: password123
     * @bodyParam password_confirmation string required Password confirmation. Example: password123
     *
     * @response 201 {
     *   "message": "User registered successfully",
     *   "data": {
     *     "user": {
     *       "id": 1,
     *       "name": "John Doe",
     *       "email": "john@example.com",
     *       "username": "johndoe"
     *     },
     *     "token": "1|abc123...",
     *     "token_type": "Bearer"
     *   }
     * }
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return response()->json([
            'message' => 'User registered successfully',
            'data' => [
                'user' => new UserTransformer($result['user']),
                'token' => $result['token'],
                'token_type' => $result['token_type'],
            ],
        ], 201);
    }

    /**
     * Login
     *
     * Authenticate a user and receive an access token.
     *
     * @unauthenticated
     *
     * @bodyParam email string required The user's email address. Example: john@example.com
     * @bodyParam password string required The user's password. Example: password123
     *
     * @response {
     *   "message": "Login successful",
     *   "data": {
     *     "user": {
     *       "id": 1,
     *       "name": "John Doe",
     *       "email": "john@example.com",
     *       "username": "johndoe"
     *     },
     *     "token": "1|abc123...",
     *     "token_type": "Bearer"
     *   }
     * }
     * @response 401 {
     *   "message": "Invalid credentials"
     * }
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            $request->validated('email'),
            $request->validated('password')
        );

        return response()->json([
            'message' => 'Login successful',
            'data' => [
                'user' => new UserTransformer($result['user']),
                'token' => $result['token'],
                'token_type' => $result['token_type'],
            ],
        ]);
    }

    /**
     * Logout
     *
     * Revoke the current access token.
     *
     * @response {
     *   "message": "Logged out successfully"
     * }
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Logout from all devices
     *
     * Revoke all access tokens for the authenticated user.
     *
     * @response {
     *   "message": "Logged out from all devices successfully"
     * }
     */
    public function logoutAll(Request $request): JsonResponse
    {
        $this->authService->logoutAll($request->user());

        return response()->json([
            'message' => 'Logged out from all devices successfully',
        ]);
    }

    /**
     * Refresh token
     *
     * Revoke current token and issue a new one.
     *
     * @response {
     *   "message": "Token refreshed successfully",
     *   "data": {
     *     "token": "2|xyz789...",
     *     "token_type": "Bearer"
     *   }
     * }
     */
    public function refresh(Request $request): JsonResponse
    {
        $result = $this->authService->refreshToken($request->user());

        return response()->json([
            'message' => 'Token refreshed successfully',
            'data' => [
                'token' => $result['token'],
                'token_type' => $result['token_type'],
            ],
        ]);
    }

    /**
     * Get current user
     *
     * Get the authenticated user's profile information.
     *
     * @response {
     *   "data": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "email": "john@example.com",
     *     "username": "johndoe",
     *     "bio": "Software developer",
     *     "profile_picture": null,
     *     "created_at": "2026-02-12T10:00:00.000000Z"
     *   }
     * }
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => new UserTransformer($request->user()),
        ]);
    }

    /**
     * Change password
     *
     * Update the authenticated user's password.
     *
     * @bodyParam current_password string required The current password. Example: password123
     * @bodyParam password string required The new password (min 8 characters). Example: newpassword123
     * @bodyParam password_confirmation string required New password confirmation. Example: newpassword123
     *
     * @response {
     *   "message": "Password changed successfully"
     * }
     * @response 422 {
     *   "message": "The current password is incorrect."
     * }
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $this->authService->changePassword(
            $request->user(),
            $request->validated('current_password'),
            $request->validated('password')
        );

        return response()->json([
            'message' => 'Password changed successfully',
        ]);
    }
}
