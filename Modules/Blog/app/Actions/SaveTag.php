<?php

namespace Modules\Blog\Actions;

use Illuminate\Validation\ValidationException;
use Modules\Blog\Interfaces\Repositories\TaxonomyRepository;
use Modules\Blog\Models\Tag;

final readonly class SaveTag
{
    public function __construct(private TaxonomyRepository $taxonomies) {}

    /**
     * @param  array{translations: array<int, array{locale: string, name: string, slug: string}>}  $data
     */
    public function handle(array $data, ?Tag $tag = null): Tag
    {
        foreach ($data['translations'] as $index => $translation) {
            if ($this->taxonomies->tagSlugExists($translation['locale'], $translation['slug'], $tag)) {
                throw ValidationException::withMessages([
                    "translations.$index.slug" => __('This slug has already been taken.'),
                ]);
            }
        }

        return $this->taxonomies->saveTag($data, $tag);
    }
}
