<?php

namespace Modules\Blog\Actions;

use Modules\Blog\Application\Posts\Data\SavePostData;
use Modules\Blog\Interfaces\Repositories\PostRepository;
use Modules\Blog\Models\Post;
use Modules\User\Models\User;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;

final readonly class SavePost
{
    public function __construct(
        private HtmlSanitizerInterface $htmlSanitizer,
        private PostRepository $posts,
    ) {}

    public function handle(User $author, SavePostData $data, ?Post $post = null): Post
    {
        $translations = array_map(
            fn (array $translation): array => [
                'locale' => $translation['locale'],
                'title' => $translation['title'],
                'summary' => $translation['summary'] ?? null,
                'body' => $this->htmlSanitizer->sanitize($translation['body']),
                'seo_title' => $translation['seo_title'] ?? null,
                'seo_description' => $translation['seo_description'] ?? null,
            ],
            $data->translations,
        );

        return $this->posts->save($author, new SavePostData(
            type: $data->type,
            translations: $translations,
            categoryIds: $data->categoryIds,
            tagIds: $data->tagIds,
        ), $post);
    }
}
