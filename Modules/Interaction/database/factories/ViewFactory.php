<?php

namespace Modules\Interaction\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Blog\Models\Post;
use Modules\Interaction\Models\View;

class ViewFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = View::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'viewable_type' => Post::class,
            'viewable_id' => Post::factory(),
            'visitor_hash' => hash('sha256', Str::uuid()->toString()),
            'viewed_on' => today(),
        ];
    }
}
