<?php

namespace Modules\Interaction\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\Blog\Models\Post;
use Modules\Blog\Transformers\PostResource;

class BookmarkedPostController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, string $locale): AnonymousResourceCollection
    {
        abort_unless(in_array($locale, config('platform.locales'), true), 404);

        $posts = Post::query()
            ->published()
            ->whereHas('translations', fn ($query) => $query->where('locale', $locale))
            ->whereHas('bookmarks', fn ($query) => $query->whereBelongsTo($request->user()))
            ->with([
                'author.skills',
                'author.media',
                'translations',
                'categories.translations',
                'tags.translations',
                'media',
            ])
            ->withCount([
                'comments' => fn ($query) => $query->approved(),
                'likes',
                'views',
            ])
            ->latest('published_at')
            ->cursorPaginate(min($request->integer('per_page', 15), 50));

        return PostResource::collection($posts);
    }
}
