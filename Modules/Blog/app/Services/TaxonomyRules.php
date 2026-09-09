<?php

namespace Modules\Blog\Services;

use Modules\Blog\Models\Category;

final class TaxonomyRules
{
    public function isOwnParent(Category $category, ?int $parentId): bool
    {
        return $parentId === $category->getKey();
    }
}
