<?php

namespace Modules\Blog\Domain\Articles\Contracts;

use Modules\Blog\Application\Articles\Data\CreateArticleData;
use Modules\Blog\Application\Articles\Data\SearchArticlesData;
use Modules\Blog\Domain\Articles\Article;
use Modules\Blog\Domain\Articles\ArticleIdentifier;

interface ArticleRepository
{
    public function create(CreateArticleData $data): Article;

    public function find(ArticleIdentifier $identifier): ?Article;

    /** @return list<Article> */
    public function search(SearchArticlesData $data): array;
}
