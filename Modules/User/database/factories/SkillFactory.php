<?php

namespace Modules\User\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\User\Models\Skill;

class SkillFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Skill::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $name = fake()->unique()->jobTitle();

        return [
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }
}
