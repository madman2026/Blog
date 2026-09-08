<?php

namespace Modules\Blog\Application\Articles\Actions;

use Modules\Blog\Application\Articles\Data\CreateArticleData;
use Modules\Blog\Domain\Articles\Article;
use Modules\Blog\Domain\Articles\Contracts\ArticleRepository;

final readonly class CreateArticle
{
    public function __construct(private ArticleRepository $articles) {}

    public function handle(CreateArticleData $data): Article
    {
        return $this->articles->create($data);
    }
}
