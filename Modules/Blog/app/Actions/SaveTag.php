<?php

namespace Modules\Blog\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Blog\Models\Tag;
use Modules\Blog\Models\TagTranslation;

class SaveTag
{
    /**
     * @param  array{translations: array<int, array{locale: string, name: string, slug: string}>}  $data
     */
    public function handle(array $data, ?Tag $tag = null): Tag
    {
        foreach ($data['translations'] as $index => $translation) {
            $exists = TagTranslation::query()
                ->where('locale', $translation['locale'])
                ->where('slug', $translation['slug'])
                ->when($tag, fn ($query) => $query->where('tag_id', '!=', $tag->getKey()))
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    "translations.$index.slug" => __('This slug has already been taken.'),
                ]);
            }
        }

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
}
