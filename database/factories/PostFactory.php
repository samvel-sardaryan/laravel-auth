<?php

namespace Database\Factories;

use App\Enums\PostStatus;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category_id' => Category::query()->inRandomOrder()->value('id'),
            'title' => fake()->sentence(6),
            'content' => fake()->paragraphs(3, true),
            'status' => PostStatus::Published,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => ['status' => PostStatus::Draft]);
    }
}
