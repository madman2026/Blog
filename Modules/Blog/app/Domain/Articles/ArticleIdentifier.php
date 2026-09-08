<?php

namespace Modules\Blog\Domain\Articles;

final readonly class ArticleIdentifier
{
    private function __construct(
        public ?int $id,
        public ?string $slug,
    ) {}

    public static function fromString(string $identifier): self
    {
        if (ctype_digit($identifier) && (int) $identifier > 0) {
            return new self(id: (int) $identifier, slug: null);
        }

        return new self(id: null, slug: $identifier);
    }
}
