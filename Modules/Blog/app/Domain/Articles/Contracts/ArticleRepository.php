<?php

namespace Modules\Blog\Domain\Articles\Contracts;

use Modules\Blog\Domain\Articles\Article;
use Modules\Blog\Domain\Articles\ArticleDraft;
use Modules\Blog\Domain\Articles\ArticleIdentifier;
use Modules\Blog\Domain\Articles\ArticleSearchCriteria;

interface ArticleRepository
{
    public function create(ArticleDraft $draft): Article;

    public function find(ArticleIdentifier $identifier): ?Article;

    /** @return list<Article> */
    public function search(ArticleSearchCriteria $criteria): array;
}
