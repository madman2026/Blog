<?php

namespace Modules\Blog\Application\Articles\Actions;

use Modules\Blog\Domain\Articles\Article;
use Modules\Blog\Domain\Articles\ArticleIdentifier;
use Modules\Blog\Domain\Articles\Contracts\ArticleRepository;

final readonly class ReadArticle
{
    public function __construct(private ArticleRepository $articles) {}

    public function handle(ArticleIdentifier $identifier): ?Article
    {
        return $this->articles->find($identifier);
    }
}
