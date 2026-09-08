<?php

namespace Modules\Blog\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Blog\Models\Tag;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            ['fa' => 'لاراول', 'en' => 'Laravel'],
            ['fa' => 'پی‌اچ‌پی', 'en' => 'PHP'],
            ['fa' => 'معماری نرم‌افزار', 'en' => 'Software Architecture'],
            ['fa' => 'یادگیری ماشین', 'en' => 'Machine Learning'],
            ['fa' => 'عامل هوشمند', 'en' => 'AI Agents'],
            ['fa' => 'دواپس', 'en' => 'DevOps'],
            ['fa' => 'رباتیک', 'en' => 'Robotics'],
            ['fa' => 'انرژی', 'en' => 'Energy'],
        ];

        foreach ($tags as $position => $names) {
            $tag = Tag::query()->firstOrCreate(['id' => $position + 1]);

            foreach ($names as $locale => $name) {
                $tag->translations()->updateOrCreate(
                    ['locale' => $locale],
                    ['name' => $name, 'slug' => Str::slug($name) ?: 'tag-'.($position + 1).'-'.$locale],
                );
            }
        }
    }
}
