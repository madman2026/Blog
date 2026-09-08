<?php

namespace Modules\Blog\Actions;

use Illuminate\Support\Facades\DB;
use Modules\Blog\Application\Posts\Data\SavePostData;
use Modules\Blog\Enums\PostStatus;
use Modules\Blog\Models\Post;
use Modules\User\Models\User;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;

class SavePost
{
    public function __construct(private readonly HtmlSanitizerInterface $htmlSanitizer) {}

    public function handle(User $author, SavePostData $data, ?Post $post = null): Post
    {
        return DB::transaction(function () use ($author, $data, $post): Post {
            $post ??= new Post([
                'author_id' => $author->getKey(),
                'status' => PostStatus::Draft,
            ]);

            $post->type = $data->type;

            if ($post->status === PostStatus::ChangesRequested) {
                $post->status = PostStatus::Draft;
                $post->reviewed_by = null;
                $post->reviewed_at = null;
            }

            $post->save();

            $locales = collect($data->translations)->pluck('locale');
            $post->translations()->whereNotIn('locale', $locales)->delete();

            foreach ($data->translations as $translation) {
                $post->translations()->updateOrCreate(
                    ['locale' => $translation['locale']],
                    [
                        'title' => $translation['title'],
                        'summary' => $translation['summary'] ?? null,
                        'body' => $this->htmlSanitizer->sanitize($translation['body']),
                        'seo_title' => $translation['seo_title'] ?? null,
                        'seo_description' => $translation['seo_description'] ?? null,
                    ],
                );
            }

            $post->categories()->sync($data->categoryIds);
            $post->tags()->sync($data->tagIds);

            return $post->load(['translations', 'categories.translations', 'tags.translations']);
        });
    }
}
