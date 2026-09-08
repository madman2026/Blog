<?php

namespace Modules\Interaction\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Blog\Models\Post;
use Modules\Interaction\Models\Bookmark;
use Modules\User\Models\User;

class BookmarkFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Bookmark::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'bookmarkable_type' => Post::class,
            'bookmarkable_id' => Post::factory(),
        ];
    }
}
