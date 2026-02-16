<?php

namespace Database\Factories;

use App\Models\Reaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reaction>
 */
class ReactionFactory extends Factory
{
    protected $model = Reaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = [
            'like', 'love', 'haha', 'wow', 'sad', 'angry'
        ];
        return [
            'user_id' => \App\Models\User::factory(),
            'reactable_id' => null, // to be set when creating
            'reactable_type' => null, // to be set when creating
            'type' => $this->faker->randomElement($types),
        ];
    }
}