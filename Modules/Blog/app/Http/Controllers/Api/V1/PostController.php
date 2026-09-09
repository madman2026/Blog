<?php

namespace Modules\Blog\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Modules\Blog\Models\Post;
use Modules\Blog\Models\PostTranslation;
use Modules\Blog\Transformers\PostResource;
use Modules\Interaction\Actions\RecordView;

class PostController extends Controller
{
    public function index(Request $request, string $locale): AnonymousResourceCollection
    {
        abort_unless(in_array($locale, config('platform.locales'), true), 404);

        $posts = Post::query()
            ->published()
            ->whereHas('translations', fn ($query) => $query->where('locale', $locale))
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

    public function show(
        Request $request,
        string $locale,
        PostTranslation $postTranslation,
        RecordView $recordView,
    ): PostResource {
        abort_unless($postTranslation->locale === $locale, 404);

        $post = $postTranslation->post()
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
            ->firstOrFail();

        Gate::authorize('view', $post);

        $user = $request->user();
        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();

        defer(fn () => $recordView->handle($post, $user, $ipAddress, $userAgent));

        return new PostResource($post);
    }
}
