<?php

namespace Modules\Blog\Domain\Articles;

final readonly class ArticleDraft
{
    public function __construct(
        public string $title,
        public string $body,
        public ?string $summary,
        public bool $published,
        public string $locale,
    ) {}
}
