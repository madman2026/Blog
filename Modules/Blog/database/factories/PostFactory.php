<?php

namespace Modules\Blog\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Blog\Models\Post;

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
            'title' => fake()->unique()->sentence(5),
            'summary' => fake()->paragraph(),
            'body' => fake()->paragraphs(5, true),
            'image' => null,
            'published' => fake()->boolean(),
        ];
    }
}
