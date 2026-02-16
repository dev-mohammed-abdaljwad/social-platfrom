<?php

namespace App\Repositories\Post\Eloquent;

use App\Enums\FriendshipStatusEnum;
use App\Enums\PrivacyTypeEnum;
use App\Models\Friendship;
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
        $query = $query->with(['user'])->withCount(['reactions', 'comments', 'shares']);

        // Add user-specific interaction checks if authenticated
        if ($userId = Auth::id()) {
            $query->withExists([
                'reactions as is_reacted' => fn($q) => $q->where('user_id', $userId),
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
        return $query->with(['user', 'comments.user', 'reactions.user', 'shares.user'])
            ->withCount(['reactions', 'comments', 'shares']);
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

    // use Cursor pagination
    public function findByUser($userId)
    {
        return $this->withListRelations($this->model->where('user_id', $userId))
            ->latest()
            ->cursorPaginate(20);
    }

    public function getFeed(User $user, ?int $lastId = null, int $limit = 20)
    {
        $friendIdsSubquery = Friendship::selectRaw('CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END', [$user->id])
            ->where('status', FriendshipStatusEnum::Accepted)
            ->where(function ($q) use ($user) {
                $q->where('sender_id', $user->id)
                    ->orWhere('receiver_id', $user->id);
            });

        $query = $this->withListRelations(
            $this->model->where(function ($q) use ($user, $friendIdsSubquery) {
                $q->where('user_id', $user->id)
                    ->orWhere('privacy', PrivacyTypeEnum::Public)
                    ->orWhere(function ($q2) use ($friendIdsSubquery) {
                        $q2->whereIn('user_id', $friendIdsSubquery)
                            ->whereIn('privacy', [PrivacyTypeEnum::Public, PrivacyTypeEnum::Friends]);
                    });
            })
        )->orderBy('id', 'desc');

        if ($lastId) {
            $query->where('id', '<', $lastId); // optional, still works as cursor
        }

        return $query->cursorPaginate($limit);
    }

    public function getPublicPosts(int $limit = 10, ?int $lastId = null)
    {
        $query = $this->withListRelations($this->model->query())
            ->where('privacy', 'public')
            ->orderBy('id', 'desc');

        if ($lastId) {
            $query->where('id', '<', $lastId);
        }

        return $query->cursorPaginate($limit);
    }

    // i want to use cursorPaginate instead of get
    public function getUserPostsWithRelations(User $user, bool $publicOnly = false)
    {
        $query = $this->withListRelations($user->posts())
            ->latest();

        if ($publicOnly) {
            $query->where('privacy', 'public');
        }

        return $query->cursorPaginate(20);
    }

    public function getUserSharedPosts(User $user)
    {
        return $user->shares()
            ->with(['post' => fn($q) => $this->withListRelations($q)])
            ->latest()
            ->cursorPaginate(20);
    }

    public function getSavedPostsForUser(User $user)
    {
        return $this->withListRelations($user->savedPosts())
            ->orderByPivot('created_at', 'desc')
            ->cursorPaginate(20);
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
