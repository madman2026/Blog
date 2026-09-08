<?php

namespace Modules\Blog\Application\Posts\Data;

use Modules\Blog\Enums\PostType;

final readonly class SavePostData
{
    /**
     * @param  array<int, array{
     *     locale: string,
     *     title: string,
     *     summary?: string|null,
     *     body: string,
     *     seo_title?: string|null,
     *     seo_description?: string|null
     * }>  $translations
     * @param  array<int, int>  $categoryIds
     * @param  array<int, int>  $tagIds
     */
    public function __construct(
        public PostType $type,
        public array $translations,
        public array $categoryIds = [],
        public array $tagIds = [],
    ) {}
}
