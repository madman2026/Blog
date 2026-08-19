<?php

namespace Modules\Interaction\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Interaction\Models\Comment;

class CommentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Comment::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [];
    }
}
