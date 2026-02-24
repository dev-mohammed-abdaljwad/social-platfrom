<?php

namespace App\Repositories\Mentions\Eloquent;

use App\Models\Mentions;
use App\Repositories\Mentions\MentionsRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentMentionsRepository implements MentionsRepository
{
    public function __construct(
        protected Mentions $model
    ) {}

    public function createMany(array $mentions): void
    {
        $this->model->insert($mentions);
    }
    public function syncForMentionable(Model $mentionable, array $mentions, int $mentionerId): void
    {
        // Used on edit — remove old mentions not in new list, add new ones
        $this->model
            ->where('mentionable_type', $mentionable->getMorphClass())
            ->where('mentionable_id', $mentionable->id)
            ->whereNotIn('mentioned_user_id', $mentions)
            ->delete();
        $existing = $this->model
            ->where('mentionable_type', $mentionable->getMorphClass())
            ->where('mentionable_id', $mentionable->id)
            ->pluck('mentioned_user_id')
            ->toArray();
        $newMentions = collect($mentions)
            ->diff($existing)
            ->map(fn($userId) => [
                'mentionable_type' => $mentionable->getMorphClass(),
                'mentionable_id' => $mentionable->id,
                'mentioned_user_id' => $userId,
                'mentioner_id' => $mentionerId,
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->values()
            ->toArray();

        if (!empty($newMentions)) {
            $this->model->insert($newMentions);
        }
    }
    public function getMentionFeed(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model
            ->where('mentioned_user_id', $userId)
            ->with([
                'mentionable',
                'mentionable.user:id,name,username,avatar',
                'mentioner:id,name,username,avatar',
            ])
            ->latest()
            ->paginate($perPage);
    }
    public function findByMentionable(Model $mentionable)
    {
        return $this->model
            ->where('mentionable_type', $mentionable->getMorphClass())
            ->where('mentionable_id', $mentionable->id)
            ->pluck('mentioned_user_id')
            ->toArray();
    }
    public function deleteForMentionable(Model $mentionable): void
    {
        $this->model->where('mentionable_type', $mentionable->getMorphClass())
            ->where('mentionable_id', $mentionable->id)
            ->delete();
    }
    public function userWasMentionedIn(int $userId, Model $mentionable): bool
    {
        return $this->model
            ->where('mentioned_user_id', $userId)
            ->where('mentionable_type', $mentionable->getMorphClass())
            ->where('mentionable_id', $mentionable->id)
            ->exists();
    }
}
