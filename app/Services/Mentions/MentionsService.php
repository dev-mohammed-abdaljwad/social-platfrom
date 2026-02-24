<?php

namespace App\Services\Mentions;


use App\Models\User;
use App\Repositories\Follow\FollowRepository;
use App\Repositories\Mentions\MentionsRepository;
use App\Services\Notification\NotificationService;
use Illuminate\Database\Eloquent\Model;

class MentionsService
{
    public function __construct(
        protected MentionsRepository $repository,
        protected FollowRepository $followRepository,
        protected NotificationService $notificationService
    ) {}
    /**
     * Extract @usernames from content and return mentionable User models.
     * Respects privacy: private accounts can only be mentioned by followers.
     */
    public function parse(string $content, int $mentionerId)
    {
        preg_match_all('/@([\w.]+)/', $content, $matches);

        $usernames = collect($matches[1])->unique()->take(10); // cap at 10 mentions

        if ($usernames->isEmpty()) {
            return collect();
        }

        return User::whereIn('username', $usernames)
            ->where('id', '!=', $mentionerId)  // can't mention yourself
            ->get()
            ->filter(function (User $user) use ($mentionerId) {
                // Public accounts: always mentionable
                if (!$user->is_private) {
                    return true;
                }
                // Private accounts: only mentionable by followers
                return $this->followRepository->existsBetween($mentionerId, $user->id);
            });
    }
    /**
     * Process mentions after a post or comment is created.
     */
    public function handleCreated(Model $mentionable, string $content, int $mentionerId): void
    {
        $mentionedUsers = $this->parse($content, $mentionerId);

        if ($mentionedUsers->isEmpty()) {
            return;
        }

        $records = $mentionedUsers->map(fn(User $user) => [
            'mentioned_user_id' => $user->id,
            'mentioner_id'      => $mentionerId,
            'mentionable_type'  => get_class($mentionable),
            'mentionable_id'    => $mentionable->id,
            'created_at'        => now(),
            'updated_at'        => now(),
        ])->values()->toArray();

        $this->repository->createMany($records);

        // Dispatch notifications asynchronously
        $mentioner = User::find($mentionerId);
        if ($mentioner) {
            foreach ($mentionedUsers as $user) {
                /** @var \App\Models\User $user */
                if ($mentionable instanceof \App\Models\Post) {
                    $this->notificationService->mentionedInPost($user, $mentioner, $mentionable);
                } elseif ($mentionable instanceof \App\Models\Comment) {
                    $this->notificationService->mentionedInComment($user, $mentioner, $mentionable);
                }
            }
        }
    }
    /**
     * Process mentions after a post or comment is edited.
     * Only notify newly added mentions — not existing ones.
     */
    public function handleUpdated(Model $mentionable, string $newContent, int $mentionerId): void
    {
        $mentionedUsers = $this->parse($newContent, $mentionerId);
        $newUserIds     = $mentionedUsers->pluck('id')->toArray();

        $existingUserIds = $this->repository
            ->findByMentionable($mentionable)
            ->toArray();

        // Sync mentions (removes stale, adds new)
        $this->repository->syncForMentionable($mentionable, $newUserIds, $mentionerId);

        // Notify only newly added mentions
        $newlyMentioned = $mentionedUsers->whereNotIn('id', $existingUserIds);

        if ($newlyMentioned->isNotEmpty()) {
            $mentioner = User::find($mentionerId);
            if ($mentioner) {
                foreach ($newlyMentioned as $user) {
                    /** @var \App\Models\User $user */
                    if ($mentionable instanceof \App\Models\Post) {
                        $this->notificationService->mentionedInPost($user, $mentioner, $mentionable);
                    } elseif ($mentionable instanceof \App\Models\Comment) {
                        $this->notificationService->mentionedInComment($user, $mentioner, $mentionable);
                    }
                }
            }
        }
    }
    public function handleDeleted(Model $mentionable): void
    {

        $this->repository->deleteForMentionable($mentionable);
    }
    /**
     * Return paginated mention feed for a user.
     */
    public function getFeed(int $userId, int $perPage = 20)
    {
        return $this->repository->getMentionFeed($userId, $perPage);
    }
    /**
     * Replace @username tokens with HTML profile links.
     * Only users who exist get linked — invalid @handles are left as plain text.
     */
    public function render(string $content, iterable $mentionedUsers): string
    {
        $content = e($content); // escape everything first

        foreach ($mentionedUsers as $user) {
            $profileUrl = route('profile.show', $user->username);
            $content = str_replace(
                "@" . $user->username,
                "<a href=\"{$profileUrl}\" class=\"mention\" data-user-id=\"{$user->id}\">@{$user->username}</a>",
                $content
            );
        }

        return $content;
    }
}
