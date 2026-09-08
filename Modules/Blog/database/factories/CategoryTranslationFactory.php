<?php

namespace Modules\Blog\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Blog\Models\Category;
use Modules\Blog\Models\CategoryTranslation;

class CategoryTranslationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = CategoryTranslation::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'category_id' => Category::factory(),
            'locale' => 'fa',
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('####'),
            'description' => fake()->optional()->sentence(),
        ];
    }
}
