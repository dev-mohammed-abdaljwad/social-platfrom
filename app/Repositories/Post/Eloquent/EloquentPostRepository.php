<?php

namespace App\Repositories\Post\Eloquent;

use App\Enums\PrivacyTypeEnum;
use App\Models\Post;
use App\Models\User;
use App\Repositories\Post\PostRepository;
use Illuminate\Support\Facades\Auth;

class EloquentPostRepository implements PostRepository
{
    public function __construct(protected Post $model) {}

    /**
     * Minimal relations for list views (just user + counts).
     */
    protected function withListRelations($query)
    {
        $query = $query->with(['user'])->withCount(['likes', 'comments', 'shares']);
        
        // Add user-specific interaction checks if authenticated
        if ($userId = Auth::id()) {
            $query->withExists([
                'likes as is_liked' => fn($q) => $q->where('user_id', $userId),
                'shares as is_shared' => fn($q) => $q->where('user_id', $userId),
                'savedBy as is_saved' => fn($q) => $q->where('user_id', $userId),
            ]);
        }
        
        return $query;
    }

    /**
     * Full relations for single post detail view.
     */
    protected function withDetailRelations($query)
    {
        return $query->with(['user', 'comments.user', 'likes.user', 'shares.user'])
            ->withCount(['likes', 'comments', 'shares']);
    }

    public function all()
    {
        return $this->withListRelations($this->model->query())
            ->latest()
            ->get();
    }

    public function find($id)
    {
        return $this->withDetailRelations($this->model->query())
            ->findOrFail($id);
    }

    public function findByUser($userId)
    {
        return $this->withListRelations($this->model->where('user_id', $userId))
            ->latest()
            ->get();
    }

    public function getFeed(User $user, int $limit = 20)
    {
        $friendIds = $user->friends()->pluck('id')->toArray();

        return $this->withListRelations(
            $this->model->where(function ($query) use ($user, $friendIds) {
                $query->where('user_id', $user->id)
                    ->orWhere('privacy', PrivacyTypeEnum::Public)
                    ->orWhere(function ($q) use ($friendIds) {
                        $q->whereIn('user_id', $friendIds)
                            ->whereIn('privacy', [PrivacyTypeEnum::Public, PrivacyTypeEnum::Friends]);
                    });
            })
        )
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function getPublicPosts(int $limit = 10)
    {
        return $this->withListRelations($this->model->query())
            ->where('privacy', 'public')
            ->orderBy('id', 'desc')
            ->take($limit)
            ->get();
    }

    public function getPublicPostsPaginated(?int $lastId = null, int $limit = 10)
    {
        $query = $this->withListRelations($this->model->query())
            ->where('privacy', 'public')
            ->orderBy('id', 'desc');

        if ($lastId) {
            $query->where('id', '<', $lastId);
        }

        return $query->take($limit)->get();
    }

    public function getUserPostsWithRelations(User $user, bool $publicOnly = false)
    {
        $query = $this->withListRelations($user->posts())
            ->latest();

        if ($publicOnly) {
            $query->where('privacy', 'public');
        }

        return $query->get();
    }

    public function getUserSharedPosts(User $user)
    {
        return $user->shares()
            ->with(['post' => fn($q) => $this->withListRelations($q)])
            ->latest()
            ->get();
    }

    public function getSavedPostsForUser(User $user)
    {
        return $this->withListRelations($user->savedPosts())
            ->orderByPivot('created_at', 'desc')
            ->get();
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($model, array $data)
    {
        $model->update($data);
        return $model;
    }

    public function delete($model)
    {
        return $model->delete();
    }
}