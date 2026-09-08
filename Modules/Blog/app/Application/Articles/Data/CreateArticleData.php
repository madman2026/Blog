<?php

namespace Modules\Blog\Application\Articles\Data;

final readonly class CreateArticleData
{
    public function __construct(
        public string $title,
        public string $body,
        public ?string $summary,
        public bool $published,
    ) {}
}
