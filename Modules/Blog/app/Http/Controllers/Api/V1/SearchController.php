<?php

namespace Modules\Blog\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use Modules\Blog\Enums\PostStatus;
use Modules\Blog\Enums\PostType;
use Modules\Blog\Models\Post;
use Modules\Blog\Models\PostTranslation;
use Modules\Blog\Transformers\PostResource;

class SearchController extends Controller
{
    public function __invoke(Request $request, string $locale): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:200'],
            'type' => ['nullable', Rule::enum(PostType::class)],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        abort_unless(in_array($locale, config('platform.locales'), true), 404);

        /** @var LengthAwarePaginator<PostTranslation> $translations */
        $translations = PostTranslation::search($validated['q'])
            ->where('locale', $locale)
            ->query(fn ($query) => $query
                ->whereHas('post', fn ($postQuery) => $postQuery
                    ->where('status', PostStatus::Published)
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now())
                    ->when(
                        isset($validated['type']),
                        fn ($typedQuery) => $typedQuery->where('type', $validated['type']),
                    ))
                ->with([
                    'post.author.skills',
                    'post.author.media',
                    'post.translations',
                    'post.categories.translations',
                    'post.tags.translations',
                    'post.media',
                ]))
            ->paginate($validated['per_page'] ?? 15);

        $posts = $translations->through(fn (PostTranslation $translation): Post => $translation->post);

        return PostResource::collection($posts);
    }
}
