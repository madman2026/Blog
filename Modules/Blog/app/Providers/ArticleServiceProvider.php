<?php

namespace Modules\Blog\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Blog\Domain\Articles\Contracts\ArticleRepository;
use Modules\Blog\Infrastructure\Persistence\EloquentArticleRepository;

class ArticleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ArticleRepository::class, EloquentArticleRepository::class);
    }
}
