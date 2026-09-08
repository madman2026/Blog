<?php

namespace Modules\Blog\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Blog\Models\Post;
use Modules\Blog\Models\PostTranslation;

class PostTranslationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = PostTranslation::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(5);

        return [
            'post_id' => Post::factory(),
            'locale' => 'fa',
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numerify('####'),
            'summary' => fake()->paragraph(),
            'body' => fake()->paragraphs(5, true),
            'seo_title' => null,
            'seo_description' => null,
        ];
    }
}
