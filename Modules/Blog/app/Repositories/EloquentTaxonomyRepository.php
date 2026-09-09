<?php

namespace Modules\Blog\Repositories;

use Illuminate\Support\Facades\DB;
use Modules\Blog\Interfaces\Repositories\TaxonomyRepository;
use Modules\Blog\Models\Category;
use Modules\Blog\Models\CategoryTranslation;
use Modules\Blog\Models\Tag;
use Modules\Blog\Models\TagTranslation;

final class EloquentTaxonomyRepository implements TaxonomyRepository
{
    public function categorySlugExists(string $locale, string $slug, ?Category $excluding = null): bool
    {
        return CategoryTranslation::query()
            ->where('locale', $locale)
            ->where('slug', $slug)
            ->when($excluding, fn ($query) => $query->where('category_id', '!=', $excluding->getKey()))
            ->exists();
    }

    public function tagSlugExists(string $locale, string $slug, ?Tag $excluding = null): bool
    {
        return TagTranslation::query()
            ->where('locale', $locale)
            ->where('slug', $slug)
            ->when($excluding, fn ($query) => $query->where('tag_id', '!=', $excluding->getKey()))
            ->exists();
    }

    public function saveCategory(array $data, ?Category $category = null): Category
    {
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

    public function saveTag(array $data, ?Tag $tag = null): Tag
    {
        return DB::transaction(function () use ($data, $tag): Tag {
            $tag ??= new Tag;
            $tag->save();

            $locales = collect($data['translations'])->pluck('locale');
            $tag->translations()->whereNotIn('locale', $locales)->delete();

            foreach ($data['translations'] as $translation) {
                $tag->translations()->updateOrCreate(
                    ['locale' => $translation['locale']],
                    ['name' => $translation['name'], 'slug' => $translation['slug']],
                );
            }

            return $tag->load('translations')->loadCount('posts');
        });
    }

    public function categoryIsInUse(Category $category): bool
    {
        return $category->children()->exists() || $category->posts()->exists();
    }

    public function tagIsInUse(Tag $tag): bool
    {
        return $tag->posts()->exists();
    }

    public function deleteCategory(Category $category): void
    {
        $category->delete();
    }

    public function deleteTag(Tag $tag): void
    {
        $tag->delete();
    }
}
