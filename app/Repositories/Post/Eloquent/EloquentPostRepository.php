<?php

namespace App\Repositories\Post\Eloquent;

use App\Enums\PrivacyTypeEnum;
use App\Models\Post;
use App\Models\User;
use App\Repositories\Post\PostRepository;

class EloquentPostRepository implements PostRepository
{
    public function __construct(protected Post $model) {}

    public function all()
    {
        return $this->model->query()
            ->with(['user'])
            ->withCount(['likes', 'comments'])
            ->latest()
            ->get();
    }

    public function find($id)
    {
        return $this->model
            ->with(['user', 'comments.user', 'likes'])
            ->withCount(['likes', 'comments'])
            ->findOrFail($id);
    }

    public function findByUser($userId)
    {
        return $this->model->where('user_id', $userId)
            ->with(['user'])
            ->withCount(['likes', 'comments'])
            ->latest()
            ->get();
    }

    public function getFeed(User $user, int $limit = 20)
    {
        $friendIds = $user->friends()->pluck('id')->toArray();

        return $this->model->where(function ($query) use ($user, $friendIds) {
            // User's own posts
            $query->where('user_id', $user->id)
                // Public posts
                ->orWhere('privacy', PrivacyTypeEnum::Public)
                // Friends' posts (public or friends-only)
                ->orWhere(function ($q) use ($friendIds) {
                    $q->whereIn('user_id', $friendIds)
                        ->whereIn('privacy', [PrivacyTypeEnum::Public, PrivacyTypeEnum::Friends]);
                });
        })
            ->with(['user'])
            ->withCount(['likes', 'comments'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function getPublicPosts(int $limit = 10)
    {
        return $this->model
            ->with(['user', 'comments', 'likes', 'shares'])
            ->where('privacy', 'public')
            ->orderBy('id', 'desc')
            ->take($limit)
            ->get();
    }

    public function getPublicPostsPaginated(?int $lastId = null, int $limit = 10)
    {
        $query = $this->model
            ->with(['user', 'comments', 'likes', 'shares'])
            ->where('privacy', 'public')
            ->orderBy('id', 'desc');

        if ($lastId) {
            $query->where('id', '<', $lastId);
        }

        return $query->take($limit)->get();
    }

    public function getUserPostsWithRelations(User $user, bool $publicOnly = false)
    {
        $query = $user->posts()
            ->with(['user', 'comments', 'likes', 'shares'])
            ->latest();

        if ($publicOnly) {
            $query->where('privacy', 'public');
        }

        return $query->get();
    }

    public function getUserSharedPosts(User $user)
    {
        return $user->shares()
            ->with(['post.user', 'post.comments', 'post.likes', 'post.shares'])
            ->latest()
            ->get();
    }

    public function getSavedPostsForUser(User $user)
    {
        return $user->savedPosts()
            ->with(['user', 'comments', 'likes', 'shares'])
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