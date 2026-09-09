<?php

namespace Modules\Blog\Actions;

use Illuminate\Validation\ValidationException;
use Modules\Blog\Interfaces\Repositories\TaxonomyRepository;
use Modules\Blog\Models\Category;
use Modules\Blog\Services\TaxonomyRules;

final readonly class SaveCategory
{
    public function __construct(
        private TaxonomyRules $rules,
        private TaxonomyRepository $taxonomies,
    ) {}

    /**
     * @param  array{parent_id?: int|null, is_active?: bool, translations: array<int, array{locale: string, name: string, slug: string, description?: string|null}>}  $data
     */
    public function handle(array $data, ?Category $category = null): Category
    {
        if ($category && $this->rules->isOwnParent($category, $data['parent_id'] ?? null)) {
            throw ValidationException::withMessages(['parent_id' => __('A category cannot be its own parent.')]);
        }

        foreach ($data['translations'] as $index => $translation) {
            if ($this->taxonomies->categorySlugExists($translation['locale'], $translation['slug'], $category)) {
                throw ValidationException::withMessages([
                    "translations.$index.slug" => __('This slug has already been taken.'),
                ]);
            }
        }

        return $this->taxonomies->saveCategory($data, $category);
    }
}
