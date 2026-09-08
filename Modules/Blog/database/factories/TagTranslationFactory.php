<?php

namespace Modules\Blog\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Blog\Models\Tag;
use Modules\Blog\Models\TagTranslation;

class TagTranslationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = TagTranslation::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'tag_id' => Tag::factory(),
            'locale' => 'fa',
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('####'),
        ];
    }
}
