<?php

namespace App\Services\Feed;

use App\Enums\FriendshipStatusEnum;
use App\Enums\PrivacyTypeEnum;
use App\Models\Friendship;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Redis;

class FeedService
{
    private const GRAVITY = 1.8;
    private const LIKE_WEIGHT = 1;
    private const COMMENT_WEIGHT = 3;
    private const SHARE_WEIGHT = 5;

    public function getSmartFeed(User $user, int $page = 1, int $perPage = 15)
    {
        $redisKey = "feed:user:{$user->id}:session";

        try {
            // Try to use Redis
            if ($page === 1) {
                $this->generateAndCacheFeed($user, $redisKey);
            }

            $start = ($page - 1) * $perPage;
            $end = $start + $perPage - 1;

            $postIds = Redis::zrevrange($redisKey, $start, $end);

            if (!empty($postIds)) {
                $idString = implode(',', $postIds);
                $query = Post::with(['user'])
                    ->withCount(['reactions', 'comments', 'shares']);

                // Preload user interaction states
                if ($user) {
                    $query->withExists([
                        'shares as is_shared' => fn($q) => $q->where('user_id', $user->id),
                        'savedBy as is_saved' => fn($q) => $q->where('user_id', $user->id),
                        'reactions as is_reacted' => fn($q) => $q->where('user_id', $user->id)
                    ])->with(['reactions' => fn($q) => $q->where('user_id', $user->id)]);
                }

                $posts = $query->whereIn('id', $postIds)
                    ->orderByRaw("FIELD(id, {$idString})")
                    ->get();

                $this->appendInteractions($posts, $user->id);
                return $posts;
            }
        } catch (\Exception $e) {
            // Redis failed or not configured, fallback to SQL calculation
            return $this->getSmartFeedFallback($user, $page, $perPage);
        }

        return collect();
    }

    private function generateAndCacheFeed(User $user, string $redisKey): void
    {
        // 1. Get friend IDs
        $friendIds = Friendship::selectRaw('CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END as friend_id', [$user->id])
            ->where('status', FriendshipStatusEnum::Accepted->value)
            ->where(function ($q) use ($user) {
                $q->where('sender_id', $user->id)
                    ->orWhere('receiver_id', $user->id);
            })
            ->pluck('friend_id');

        // 2. Fetch Candidates (posts from friends in last 72 hours)
        $candidatesQuery = Post::select('id', 'likes_count', 'comments_count', 'shares_count', 'created_at')
            ->where(function ($q) use ($user, $friendIds) {
                $q->where('user_id', $user->id)
                    ->orWhere(function ($q2) use ($friendIds) {
                        $q2->whereIn('user_id', $friendIds)
                            ->whereIn('privacy', [PrivacyTypeEnum::Public->value, PrivacyTypeEnum::Friends->value]);
                    });
            })
            ->where('created_at', '>=', now()->subDays(3));

        $candidates = $candidatesQuery->get();

        // 3. Clear existing simple cache
        Redis::del($redisKey);
        Redis::expire($redisKey, 3600); // expire after 1 hour

        // 4. Score and insert into Redis ZSET
        if ($candidates->isEmpty()) {
            return;
        }

        $pipeline = Redis::pipeline();
        foreach ($candidates as $post) {
            $score = $this->calculateScore($post);
            $pipeline->zadd($redisKey, $score, $post->id);
        }
        $pipeline->execute();
    }

    private function calculateScore($post): float
    {
        $engagement = ($post->likes_count * self::LIKE_WEIGHT) +
            ($post->comments_count * self::COMMENT_WEIGHT) +
            ($post->shares_count * self::SHARE_WEIGHT) + 1; // +1 base value

        $ageInHours = max(1, $post->created_at->diffInHours(now()));

        $timeDecay = 1 / pow($ageInHours + 2, self::GRAVITY);

        // Feed Score
        return $engagement * $timeDecay;
    }

    private function getSmartFeedFallback(User $user, int $page, int $perPage)
    {
        $friendIds = Friendship::selectRaw('CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END as friend_id', [$user->id])
            ->where('status', FriendshipStatusEnum::Accepted->value)
            ->where(function ($q) use ($user) {
                $q->where('sender_id', $user->id)
                    ->orWhere('receiver_id', $user->id);
            })
            ->pluck('friend_id');

        $gravity = self::GRAVITY;
        $query = Post::with(['user'])
            ->withCount(['reactions', 'comments', 'shares'])
            ->selectRaw("posts.*, ( (likes_count * ? + comments_count * ? + shares_count * ? + 1) / 
                POWER(TIMESTAMPDIFF(HOUR, created_at, NOW()) + 2, ?) ) as feed_score", [
                self::LIKE_WEIGHT,
                self::COMMENT_WEIGHT,
                self::SHARE_WEIGHT,
                $gravity
            ]);

        if ($user) {
            $query->withExists([
                'shares as is_shared' => fn($q) => $q->where('user_id', $user->id),
                'savedBy as is_saved' => fn($q) => $q->where('user_id', $user->id),
                'reactions as is_reacted' => fn($q) => $q->where('user_id', $user->id)
            ])->with(['reactions' => fn($q) => $q->where('user_id', $user->id)]);
        }

        $posts = $query->where(function ($q) use ($user, $friendIds) {
            $q->where('user_id', $user->id)
                ->orWhere(function ($q2) use ($friendIds) {
                    $q2->whereIn('user_id', $friendIds)
                        ->whereIn('privacy', [PrivacyTypeEnum::Public->value, PrivacyTypeEnum::Friends->value]);
                });
        })
            ->orderByDesc('feed_score')
            ->forPage($page, $perPage)
            ->get();

        $this->appendInteractions($posts, $user->id);

        return $posts;
    }

    private function appendInteractions($posts, int $userId): void
    {
        if ($posts->isEmpty()) return;

        // Fetch all grouped reaction counts for these posts in a single query
        $postIds = $posts->pluck('id')->toArray();
        $reactionGroups = \Illuminate\Support\Facades\DB::table('reactions')
            ->select('reactable_id', 'type', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
            ->where('reactable_type', Post::class)
            ->whereIn('reactable_id', $postIds)
            ->groupBy('reactable_id', 'type')
            ->get()
            ->groupBy('reactable_id');

        foreach ($posts as $post) {
            // is_reacted, is_shared, is_saved are already hydrated by withExists in query.
            // Ensure they fall back to false if missing (e.g. from tests or different context)
            if (!isset($post->is_reacted)) $post->is_reacted = false;
            if (!isset($post->is_shared)) $post->is_shared = false;
            if (!isset($post->is_saved)) $post->is_saved = false;

            // Attach pre-calculated grouped reactions for the Blade template
            $post->reactionCountsGrouped = $reactionGroups->get($post->id, collect([]));
        }
    }
}
