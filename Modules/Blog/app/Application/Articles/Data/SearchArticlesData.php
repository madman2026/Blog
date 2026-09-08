<?php

namespace Modules\Blog\Application\Articles\Data;

final readonly class SearchArticlesData
{
    public function __construct(
        public string $query,
        public int $limit,
        public ?bool $published,
    ) {}
}
