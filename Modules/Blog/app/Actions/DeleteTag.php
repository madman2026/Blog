<?php

namespace Modules\Blog\Actions;

use Illuminate\Validation\ValidationException;
use Modules\Blog\Interfaces\Repositories\TaxonomyRepository;
use Modules\Blog\Models\Tag;

final readonly class DeleteTag
{
    public function __construct(private TaxonomyRepository $taxonomies) {}

    public function handle(Tag $tag): void
    {
        if ($this->taxonomies->tagIsInUse($tag)) {
            throw ValidationException::withMessages([
                'tag' => __('A tag in use cannot be deleted.'),
            ]);
        }

        $this->taxonomies->deleteTag($tag);
    }
}
