<?php

namespace Modules\Blog\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Blog\Models\Category;
use Modules\Blog\Models\CategoryTranslation;

class SaveCategory
{
    /**
     * @param  array{parent_id?: int|null, is_active?: bool, translations: array<int, array{locale: string, name: string, slug: string, description?: string|null}>}  $data
     */
    public function handle(array $data, ?Category $category = null): Category
    {
        if ($category && ($data['parent_id'] ?? null) === $category->getKey()) {
            throw ValidationException::withMessages(['parent_id' => __('A category cannot be its own parent.')]);
        }

        foreach ($data['translations'] as $index => $translation) {
            $exists = CategoryTranslation::query()
                ->where('locale', $translation['locale'])
                ->where('slug', $translation['slug'])
                ->when($category, fn ($query) => $query->where('category_id', '!=', $category->getKey()))
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    "translations.$index.slug" => __('This slug has already been taken.'),
                ]);
            }
        }

        return DB::transaction(function () use ($data, $category): Category {
            $category ??= new Category;
            $category->fill([
                'parent_id' => $data['parent_id'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ])->save();

            $locales = collect($data['translations'])->pluck('locale');
            $category->translations()->whereNotIn('locale', $locales)->delete();

            foreach ($data['translations'] as $translation) {
                $category->translations()->updateOrCreate(
                    ['locale' => $translation['locale']],
                    [
                        'name' => $translation['name'],
                        'slug' => $translation['slug'],
                        'description' => $translation['description'] ?? null,
                    ],
                );
            }

            return $category->load('translations')->loadCount(['children', 'posts']);
        });
    }
}
