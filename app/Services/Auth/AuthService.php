<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Repositories\User\UserRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        protected UserRepository $userRepository
    ) {}

    /**
     * Register a new user and create API token.
     */
    public function register(array $data): array
    {
        $user = $this->userRepository->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'username' => $data['username'],
            'password' => $data['password'],
            'bio' => $data['bio'] ?? null,
            'phone' => $data['phone'] ?? null,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ];
    }

    /**
     * Register a new user for web (session-based).
     */
    public function registerWeb(array $data): User
    {
        $user = $this->userRepository->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'username' => $data['username'],
            'password' => $data['password'],
        ]);

        Auth::login($user);

        return $user;
    }

    /**
     * Login user and create API token.
     */
    public function login(string $email, string $password): array
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Your account has been deactivated.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ];
    }

    /**
     * Login user for web (session-based).
     */
    public function loginWeb(string $email, string $password, bool $remember = false): array
    {
        $credentials = ['email' => $email, 'password' => $password];

        if (Auth::attempt($credentials, $remember)) {
            return ['success' => true, 'message' => 'Login successful'];
        }

        return ['success' => false, 'message' => 'The provided credentials do not match our records.'];
    }

    /**
     * Logout user from web session.
     */
    public function logoutWeb(): void
    {
        Auth::logout();
    }

    /**
     * Logout user by revoking current token.
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    /**
     * Logout user from all devices by revoking all tokens.
     */
    public function logoutAll(User $user): void
    {
        $user->tokens()->delete();
    }

    /**
     * Refresh the current token.
     */
    public function refreshToken(User $user): array
    {
        // Delete current token
        $user->currentAccessToken()->delete();
        
        // Create new token
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ];
    }

    /**
     * Change user password.
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword): bool
    {
        if (!Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        $this->userRepository->update($user, ['password' => $newPassword]);

        return true;
    }
}
