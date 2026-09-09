<?php

namespace Modules\Blog\Actions;

use Illuminate\Validation\ValidationException;
use Modules\Blog\Interfaces\Repositories\TaxonomyRepository;
use Modules\Blog\Models\Category;

final readonly class DeleteCategory
{
    public function __construct(private TaxonomyRepository $taxonomies) {}

    public function handle(Category $category): void
    {
        if ($this->taxonomies->categoryIsInUse($category)) {
            throw ValidationException::withMessages([
                'category' => __('A category in use cannot be deleted.'),
            ]);
        }

        $this->taxonomies->deleteCategory($category);
    }
}
