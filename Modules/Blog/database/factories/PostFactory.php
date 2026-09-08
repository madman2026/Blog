<?php

namespace Modules\Blog\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Blog\Enums\PostStatus;
use Modules\Blog\Enums\PostType;
use Modules\Blog\Models\Post;
use Modules\User\Models\User;

class PostFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Post::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'author_id' => User::factory(),
            'type' => PostType::Article,
            'status' => PostStatus::Draft,
            'reviewed_by' => null,
            'review_notes' => null,
            'submitted_at' => null,
            'reviewed_at' => null,
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => PostStatus::Published,
            'published_at' => now(),
        ]);
    }
}
