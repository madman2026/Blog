<?php

namespace Modules\Blog\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Blog\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['fa' => 'مهندسی نرم‌افزار', 'en' => 'Software Engineering'],
            ['fa' => 'هوش مصنوعی', 'en' => 'Artificial Intelligence'],
            ['fa' => 'امنیت', 'en' => 'Cybersecurity'],
            ['fa' => 'مهندسی مکانیک', 'en' => 'Mechanical Engineering'],
            ['fa' => 'رایانش ابری', 'en' => 'Cloud Computing'],
            ['fa' => 'علم داده', 'en' => 'Data Science'],
        ];

        foreach ($categories as $position => $names) {
            $category = Category::query()->firstOrCreate(['id' => $position + 1], ['is_active' => true]);

            foreach ($names as $locale => $name) {
                $category->translations()->updateOrCreate(
                    ['locale' => $locale],
                    ['name' => $name, 'slug' => Str::slug($name) ?: 'category-'.($position + 1).'-'.$locale],
                );
            }
        }
    }
}
