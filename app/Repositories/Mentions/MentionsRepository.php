<?php

namespace App\Repositories\Mentions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface MentionsRepository
{
    public function createMany(array $mentions): void;
    public function syncForMentionable(Model $mentionable, array $mentions, int $mentionerId): void;
    public function getMentionFeed(int $userId, int $perPage = 15): LengthAwarePaginator;
    public function findByMentionable(Model $mentionable);
    public function deleteForMentionable(Model $mentionable): void;
    public function userWasMentionedIn(int $userId, Model $mentionable): bool;
}
