<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Repositories
use App\Repositories\User\UserRepository;
use App\Repositories\User\Eloquent\EloquentUserRepository;
use App\Repositories\Post\PostRepository;
use App\Repositories\Post\Eloquent\EloquentPostRepository;
use App\Repositories\Comment\CommentRepository;
use App\Repositories\Comment\Eloquent\EloquentCommentRepository;
use App\Repositories\Like\LikeRepository;
use App\Repositories\Like\Eloquent\EloquentLikeRepository;
use App\Repositories\Friendship\FriendshipRepository;
use App\Repositories\Friendship\Eloquent\EloquentFriendshipRepository;

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
        LikeRepository::class => EloquentLikeRepository::class,
        FriendshipRepository::class => EloquentFriendshipRepository::class,
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
