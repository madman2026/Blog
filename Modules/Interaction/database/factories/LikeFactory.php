<?php

namespace Modules\Interaction\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Interaction\Models\Like;

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
        return [];
    }
}
