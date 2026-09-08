<?php

namespace Modules\Blog\Application\Articles\Actions;

use Modules\Blog\Application\Articles\Data\SearchArticlesData;
use Modules\Blog\Domain\Articles\Article;
use Modules\Blog\Domain\Articles\Contracts\ArticleRepository;

final readonly class SearchArticles
{
    public function __construct(private ArticleRepository $articles) {}

    /** @return list<Article> */
    public function handle(SearchArticlesData $data): array
    {
        return $this->articles->search($data);
    }
}
