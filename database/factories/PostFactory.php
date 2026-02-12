<?php

namespace Database\Factories;

use App\Enums\ContentTypeEnum;
use App\Enums\PrivacyTypeEnum;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    protected $model = Post::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'content' => fake()->paragraphs(rand(1, 3), true),
            'image' => null,
            'video' => null,
            'location' => fake()->optional(0.3)->city(),
            'privacy' => fake()->randomElement(PrivacyTypeEnum::getValues()),
            'type' => ContentTypeEnum::Text->value,
        ];
    }

    /**
     * Indicate that the post has an image.
     */
    public function withImage(): static
    {
        return $this->state(fn (array $attributes) => [
            'image' => fake()->imageUrl(800, 600, 'social'),
            'type' => ContentTypeEnum::Image->value,
        ]);
    }

    /**
     * Indicate that the post has a video.
     */
    public function withVideo(): static
    {
        return $this->state(fn (array $attributes) => [
            'video' => 'https://example.com/videos/' . fake()->uuid() . '.mp4',
            'type' => ContentTypeEnum::Video->value,
        ]);
    }

    /**
     * Indicate that the post is public.
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'privacy' => PrivacyTypeEnum::Public->value,
        ]);
    }

    /**
     * Indicate that the post is private.
     */
    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'privacy' => PrivacyTypeEnum::Privet->value,
        ]);
    }

    /**
     * Indicate that the post is visible to friends only.
     */
    public function friendsOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'privacy' => PrivacyTypeEnum::Friends->value,
        ]);
    }
}
