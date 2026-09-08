<?php

namespace Modules\Interaction\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Blog\Models\Post;
use Modules\Interaction\Enums\CommentStatus;
use Modules\Interaction\Models\Comment;
use Modules\User\Models\User;

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
        return [
            'user_id' => User::factory(),
            'parent_id' => null,
            'commentable_type' => Post::class,
            'commentable_id' => Post::factory(),
            'body' => fake()->paragraph(),
            'status' => CommentStatus::Pending,
            'moderated_by' => null,
            'moderated_at' => null,
            'moderation_notes' => null,
        ];
    }
}
