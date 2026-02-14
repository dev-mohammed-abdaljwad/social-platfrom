<?php

namespace App\Services\Post;

use App\Models\Post;
use App\Models\User;
use App\Repositories\Post\PostRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PostService
{
    public function __construct(
        protected PostRepository $repository
    ) {}

    public function all()
    {
        return $this->repository->all();
    }

    public function find($id)
    {
        return $this->repository->find($id);
    }

    public function findByUser($userId)
    {
        return $this->repository->findByUser($userId);
    }

    public function getFeed(User $user, ?int $lastId = null, int $limit = 20)
    {
        return $this->repository->getFeed($user, $lastId, $limit);
    }

    public function getPublicPosts(int $limit = 10, ?int $lastId = null)
    {
        return $this->repository->getPublicPosts($limit, $lastId);
    }

    public function getUserPostsWithRelations(User $user, bool $publicOnly = false)
    {
        return $this->repository->getUserPostsWithRelations($user, $publicOnly);
    }

    public function getUserSharedPosts(User $user)
    {
        return $this->repository->getUserSharedPosts($user);
    }

    public function getSavedPostsForUser(User $user)
    {
        return $this->repository->getSavedPostsForUser($user);
    }

    public function create(array $data)
    {
        return $this->repository->create($data);
    }

    public function createForUser(User $user, array $data)
    {
        return $this->repository->create([
            'user_id' => $user->id,
            'content' => $data['content'],
            'image' => $data['image'] ?? null,
            'video' => $data['video'] ?? null,
            'location' => $data['location'] ?? null,
            'privacy' => $data['privacy'] ?? 'public',
            'type' => $data['type'] ?? 'text',
        ]);
    }

    /**
     * Create a post with file upload handling.
     */
    public function createWithMedia(User $user, array $data, ?UploadedFile $image = null, ?UploadedFile $video = null): Post
    {
        $type = 'text';
        $imagePath = null;
        $videoPath = null;

        // Handle video upload (priority over image)
        if ($video) {
            $videoPath = $video->store('posts/videos', 'public');
            $type = 'video';
        }
        // Handle image upload (only if no video)
        elseif ($image) {
            $imagePath = $image->store('posts/images', 'public');
            $type = 'image';
        }

        return $this->repository->create([
            'user_id' => $user->id,
            'content' => $data['content'] ?? null,
            'image' => $imagePath,
            'video' => $videoPath,
            'location' => $data['location'] ?? null,
            'privacy' => $data['privacy'] ?? 'public',
            'type' => $type,
        ]);
    }

    public function update($model, array $data)
    {
        return $this->repository->update($model, $data);
    }

    public function delete($model)
    {
        return $this->repository->delete($model);
    }

    /**
     * Delete a post and its associated media.
     */
    public function deleteWithMedia($post): bool
    {
        // Delete associated media files
        if ($post->image) {
            Storage::disk('public')->delete($post->image);
        }
        if ($post->video) {
            Storage::disk('public')->delete($post->video);
        }

        return $this->repository->delete($post);
    }

    /**
     * Check if user can modify the post.
     */
    public function canModify(int $postUserId, int $authUserId): bool
    {
        return $postUserId === $authUserId;
    }

    /**
     * Toggle save on a post for a user.
     */
    public function toggleSave(User $user, Post $post): array
    {
        if ($post->isSavedBy($user)) {
            $user->savedPosts()->detach($post->id);
            return ['saved' => false];
        }

        $user->savedPosts()->attach($post->id);
        return ['saved' => true];
    }

    /**
     * Get formatted likes for a post.
     */
    public function getFormattedLikes(Post $post): array
    {
        $likes = $post->likes()->with('user')->get();

        return $likes->map(fn($like) => [
            'id' => $like->id,
            'user' => [
                'id' => $like->user->id,
                'name' => $like->user->name,
                'username' => $like->user->username,
                'avatar_url' => $like->user->avatar_url,
            ],
            'created_at' => $like->created_at->toISOString(),
        ])->toArray();
    }

    /**
     * Format a post for JSON response.
     */
    public function formatPost(Post $post): array
    {
        return [
            'id' => $post->id,
            'content' => $post->content,
            'location' => $post->location,
            'privacy' => $post->privacy,
        ];
    }
}
