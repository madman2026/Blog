<?php

namespace Modules\Blog\Interfaces\Repositories;

use Modules\Blog\Models\Category;
use Modules\Blog\Models\Tag;

interface TaxonomyRepository
{
    public function categorySlugExists(string $locale, string $slug, ?Category $excluding = null): bool;

    public function tagSlugExists(string $locale, string $slug, ?Tag $excluding = null): bool;

    /** @param array<string, mixed> $data */
    public function saveCategory(array $data, ?Category $category = null): Category;

    /** @param array<string, mixed> $data */
    public function saveTag(array $data, ?Tag $tag = null): Tag;

    public function categoryIsInUse(Category $category): bool;

    public function tagIsInUse(Tag $tag): bool;

    public function deleteCategory(Category $category): void;

    public function deleteTag(Tag $tag): void;
}
