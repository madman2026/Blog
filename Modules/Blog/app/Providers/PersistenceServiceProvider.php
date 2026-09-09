<?php

namespace Modules\Blog\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Blog\Domain\Articles\Contracts\ArticleRepository;
use Modules\Blog\Infrastructure\Persistence\EloquentArticleRepository;
use Modules\Blog\Interfaces\Repositories\PostRepository;
use Modules\Blog\Interfaces\Repositories\TaxonomyRepository;
use Modules\Blog\Repositories\EloquentPostRepository;
use Modules\Blog\Repositories\EloquentTaxonomyRepository;

final class PersistenceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ArticleRepository::class, EloquentArticleRepository::class);
        $this->app->bind(PostRepository::class, EloquentPostRepository::class);
        $this->app->bind(TaxonomyRepository::class, EloquentTaxonomyRepository::class);
    }
}
