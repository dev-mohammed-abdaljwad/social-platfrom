<?php

namespace Database\Factories;

use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Like>
 */
class LikeFactory extends Factory
{
    protected $model = Like::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'likeable_id' => Post::factory(),
            'likeable_type' => Post::class,
        ];
    }

    /**
     * Configure the like to be for a post.
     */
    public function forPost(Post $post = null): static
    {
        return $this->state(fn (array $attributes) => [
            'likeable_id' => $post?->id ?? Post::factory(),
            'likeable_type' => Post::class,
        ]);
    }

    /**
     * Configure the like to be for a comment.
     */
    public function forComment($comment): static
    {
        return $this->state(fn (array $attributes) => [
            'likeable_id' => $comment->id,
            'likeable_type' => \App\Models\Comment::class,
        ]);
    }
}
