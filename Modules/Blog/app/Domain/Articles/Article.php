<?php

namespace Modules\Blog\Domain\Articles;

final readonly class Article
{
    public function __construct(
        public int $id,
        public string $locale,
        public string $title,
        public string $slug,
        public ?string $summary,
        public string $body,
        public bool $published,
        public string $createdAt,
        public string $updatedAt,
    ) {}

    /**
     * @return array{
     *     id: int,
     *     locale: string,
     *     title: string,
     *     slug: string,
     *     summary: string|null,
     *     body: string,
     *     published: bool,
     *     created_at: string,
     *     updated_at: string
     * }
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'locale' => $this->locale,
            'title' => $this->title,
            'slug' => $this->slug,
            'summary' => $this->summary,
            'body' => $this->body,
            'published' => $this->published,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
