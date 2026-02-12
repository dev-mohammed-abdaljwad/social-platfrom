<?php

namespace Database\Factories;

use App\Enums\FriendshipStatusEnum;
use App\Models\Friendship;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Friendship>
 */
class FriendshipFactory extends Factory
{
    protected $model = Friendship::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sender_id' => User::factory(),
            'receiver_id' => User::factory(),
            'status' => FriendshipStatusEnum::Pending->value,
        ];
    }

    /**
     * Indicate that the friendship is accepted.
     */
    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => FriendshipStatusEnum::Accepted->value,
        ]);
    }

    /**
     * Indicate that the friendship is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => FriendshipStatusEnum::Pending->value,
        ]);
    }

    /**
     * Indicate that the friendship is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => FriendshipStatusEnum::Rejected->value,
        ]);
    }

    /**
     * Indicate that the friendship is blocked.
     */
    public function blocked(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => FriendshipStatusEnum::Blocked->value,
        ]);
    }
}
