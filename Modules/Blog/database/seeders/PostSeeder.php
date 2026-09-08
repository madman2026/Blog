<?php

namespace Modules\Blog\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Blog\Enums\PostStatus;
use Modules\Blog\Enums\PostType;
use Modules\Blog\Models\Category;
use Modules\Blog\Models\Post;
use Modules\Blog\Models\Tag;
use Modules\User\Enums\UserRole;
use Modules\User\Models\User;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $author = User::query()->updateOrCreate(
            ['email' => 'author@example.com'],
            [
                'username' => 'tech_author',
                'phone' => '+989120000001',
                'password' => 'password',
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'bio' => 'Software engineer and technology writer.',
                'about' => 'I write practical guides about modern software engineering and emerging technology.',
                'social_links' => ['github' => 'https://github.com/laravel'],
                'preferred_locale' => 'fa',
            ],
        );
        $author->syncRoles(UserRole::Author->value);

        $publications = [
            ['article', 'معماری ماژولار در لاراول مدرن', 'Modern modular architecture in Laravel', 'چگونه یک monolith ماژولار و قابل توسعه بسازیم.', 'How to build an extensible modular monolith.'],
            ['news', 'روندهای مهم هوش مصنوعی برای مهندسان', 'AI trends that matter to engineers', 'مروری کوتاه و دقیق بر روندهایی که ارزش دنبال کردن دارند.', 'A concise look at the trends worth following.'],
            ['article', 'صف‌ها و پردازش پس‌زمینه با Redis', 'Background processing with Redis queues', 'الگوهای پایدار برای jobها، retry و مانیتورینگ.', 'Reliable patterns for jobs, retries and monitoring.'],
            ['article', 'از مدل زبانی تا عامل هوشمند', 'From language models to AI agents', 'اجزای اصلی یک سیستم agentic و مرزهای کاربرد آن.', 'The building blocks and practical boundaries of agentic systems.'],
            ['news', 'چرا WebAssembly دوباره مهم شده است', 'Why WebAssembly matters again', 'کاربردهای تازه WebAssembly در وب، سرور و ابزارهای مهندسی.', 'New WebAssembly use cases across web, server and engineering tools.'],
            ['article', 'مبانی دیجیتال تویین برای مهندسان', 'Digital twin fundamentals for engineers', 'پیوند داده، شبیه‌سازی و تصمیم‌گیری در سامانه‌های فیزیکی.', 'Connecting data, simulation and decisions in physical systems.'],
        ];

        foreach ($publications as $position => [$type, $faTitle, $enTitle, $faSummary, $enSummary]) {
            $enSlug = Str::slug($enTitle);
            $post = Post::query()
                ->whereHas('translations', fn ($query) => $query->where('locale', 'en')->where('slug', $enSlug))
                ->first();

            if (! $post) {
                $post = Post::query()->create([
                    'author_id' => $author->getKey(),
                    'type' => PostType::from($type),
                    'status' => PostStatus::Published,
                    'published_at' => now()->subDays(count($publications) - $position),
                ]);
            }

            $post->translations()->updateOrCreate(['locale' => 'fa'], [
                'title' => $faTitle,
                'slug' => 'publication-'.($position + 1).'-fa',
                'summary' => $faSummary,
                'body' => '<h2>'.$faTitle.'</h2><p>'.$faSummary.'</p><p>این محتوای نمونه برای توسعه رابط و بررسی چرخه کامل انتشار ایجاد شده است.</p>',
            ]);
            $post->translations()->updateOrCreate(['locale' => 'en'], [
                'title' => $enTitle,
                'slug' => $enSlug,
                'summary' => $enSummary,
                'body' => '<h2>'.$enTitle.'</h2><p>'.$enSummary.'</p><p>This development fixture exercises the complete publication workflow and interface.</p>',
            ]);
            $post->categories()->sync([Category::query()->inRandomOrder()->value('id')]);
            $post->tags()->sync(Tag::query()->inRandomOrder()->limit(3)->pluck('id'));
        }
    }
}
