<?php

namespace App\Services\User;

use App\Models\User;
use App\Repositories\User\UserRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserService
{
    public function __construct(
        protected UserRepository $repository
    ) {}

    public function all()
    {
        return $this->repository->all();
    }

    public function find($id)
    {
        return $this->repository->find($id);
    }

    public function findByEmail(string $email)
    {
        return $this->repository->findByEmail($email);
    }

    public function findByUsername(string $username)
    {
        return $this->repository->findByUsername($username);
    }

    public function create(array $data)
    {
        return $this->repository->create($data);
    }

    public function update($model, array $data)
    {
        return $this->repository->update($model, $data);
    }

    public function delete($model)
    {
        return $this->repository->delete($model);
    }

    public function search(string $query, int $limit = 20)
    {
        return $this->repository->search($query, $limit);
    }

    public function updateProfile($user, array $data)
    {
        $allowedFields = ['name', 'bio', 'phone', 'profile_picture'];
        $filteredData = array_intersect_key($data, array_flip($allowedFields));
        
        return $this->repository->update($user, $filteredData);
    }

    public function getSuggestions(User $user, array $excludeIds, int $limit = 6)
    {
        return $this->repository->getSuggestions($user, $excludeIds, $limit);
    }

    /**
     * Update user profile information.
     */
    public function updateProfileInfo(User $user, array $data): User
    {
        $this->repository->update($user, $data);
        return $user->fresh();
    }

    /**
     * Update user email with password verification.
     */
    public function updateEmail(User $user, string $email, string $password): array
    {
        if (!$this->verifyPassword($user, $password)) {
            return ['success' => false, 'message' => 'The provided password is incorrect.'];
        }

        $this->repository->update($user, ['email' => $email]);

        return ['success' => true, 'message' => 'Email updated successfully'];
    }

    /**
     * Update user password with current password verification.
     */
    public function updatePassword(User $user, string $currentPassword, string $newPassword): array
    {
        if (!$this->verifyPassword($user, $currentPassword)) {
            return ['success' => false, 'message' => 'The current password is incorrect.'];
        }

        $this->repository->update($user, ['password' => $newPassword]);

        return ['success' => true, 'message' => 'Password updated successfully'];
    }

    /**
     * Delete user account with password verification.
     */
    public function deleteAccount(User $user, string $password): array
    {
        if (!$this->verifyPassword($user, $password)) {
            return ['success' => false, 'message' => 'The provided password is incorrect.'];
        }

        // Delete profile picture
        $this->deleteUserFile($user->profile_picture);

        // Delete cover photo
        $this->deleteUserFile($user->cover_photo);

        // Delete all user tokens
        $user->tokens()->delete();

        // Delete user
        $this->repository->delete($user);

        return ['success' => true, 'message' => 'Account deleted successfully'];
    }

    /**
     * Update user profile picture.
     */
    public function updateProfilePicture(User $user, UploadedFile $file): array
    {
        // Delete old profile picture if exists
        $this->deleteUserFile($user->profile_picture);

        // Store new profile picture
        $path = $file->store('profile-pictures', 'public');

        $this->repository->update($user, ['profile_picture' => $path]);

        return [
            'success' => true,
            'message' => 'Profile picture updated successfully',
            'profile_picture_url' => $user->fresh()->avatar_url,
        ];
    }

    /**
     * Update user cover photo.
     */
    public function updateCoverPhoto(User $user, UploadedFile $file): array
    {
        // Delete old cover photo if exists
        $this->deleteUserFile($user->cover_photo);

        // Store new cover photo
        $path = $file->store('cover-photos', 'public');

        $this->repository->update($user, ['cover_photo' => $path]);

        return [
            'success' => true,
            'message' => 'Cover photo updated successfully',
            'cover_photo_url' => $user->fresh()->cover_url,
        ];
    }

    /**
     * Remove user profile picture.
     */
    public function removeProfilePicture(User $user): array
    {
        $this->deleteUserFile($user->profile_picture);

        $this->repository->update($user, ['profile_picture' => null]);

        return [
            'success' => true,
            'message' => 'Profile picture removed',
            'profile_picture_url' => $user->fresh()->avatar_url,
        ];
    }

    /**
     * Remove user cover photo.
     */
    public function removeCoverPhoto(User $user): array
    {
        $this->deleteUserFile($user->cover_photo);

        $this->repository->update($user, ['cover_photo' => null]);

        return [
            'success' => true,
            'message' => 'Cover photo removed',
            'cover_photo_url' => null,
        ];
    }

    /**
     * Verify user password.
     */
    public function verifyPassword(User $user, string $password): bool
    {
        return Hash::check($password, $user->password);
    }

    /**
     * Delete a user file from storage.
     */
    protected function deleteUserFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
