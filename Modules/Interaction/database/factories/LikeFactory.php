<?php

namespace Modules\Interaction\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Blog\Models\Post;
use Modules\Interaction\Models\Like;
use Modules\User\Models\User;

class LikeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Like::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'likeable_type' => Post::class,
            'likeable_id' => Post::factory(),
        ];
    }
}
