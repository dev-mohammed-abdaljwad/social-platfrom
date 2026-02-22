<?php

namespace App\Providers;

use App\Repositories\Chat\ChatRepository;
use App\Repositories\Chat\Eloquent\EloquentChatRepository;
use Illuminate\Support\ServiceProvider;
use App\Repositories\User\UserRepository;
use App\Repositories\User\Eloquent\EloquentUserRepository;
use App\Repositories\Post\PostRepository;
use App\Repositories\Post\Eloquent\EloquentPostRepository;
use App\Repositories\Comment\CommentRepository;
use App\Repositories\Comment\Eloquent\EloquentCommentRepository;
use App\Repositories\Follow\Eloquent\EloquentFollowRepository;
use App\Repositories\Follow\FollowRepository;
use App\Repositories\Friendship\FriendshipRepository;
use App\Repositories\Friendship\Eloquent\EloquentFriendshipRepository;
use App\Repositories\Message\Eloquent\EloquentMessageRepository;
use App\Repositories\Message\MessageRepository;
use App\Repositories\Share\ShareRepository;
use App\Repositories\Share\Eloquent\EloquentShareRepository;
use App\Repositories\Reaction\ReactionRepository;
use App\Repositories\Reaction\Eloquent\EloquentReactionRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * All repository bindings.
     *
     * @var array<class-string, class-string>
     */
    protected array $repositories = [
        UserRepository::class => EloquentUserRepository::class,
        PostRepository::class => EloquentPostRepository::class,
        CommentRepository::class => EloquentCommentRepository::class,
        FriendshipRepository::class => EloquentFriendshipRepository::class,
        ShareRepository::class => EloquentShareRepository::class,
        ReactionRepository::class => EloquentReactionRepository::class,
        ChatRepository::class => EloquentChatRepository::class,
        FollowRepository::class => EloquentFollowRepository::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        foreach ($this->repositories as $interface => $implementation) {
            $this->app->bind($interface, $implementation);
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
