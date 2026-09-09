<?php

namespace Modules\Blog\Domain\Articles;

final readonly class ArticleSearchCriteria
{
    public function __construct(
        public string $query,
        public int $limit,
        public ?bool $published,
    ) {}
}
