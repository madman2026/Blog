<?php

namespace Modules\Blog\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Modules\Blog\Actions\SavePost;
use Modules\Blog\Application\Posts\Data\SavePostData;
use Modules\Blog\Enums\PostType;
use Modules\Blog\Http\Requests\Api\V1\SavePostRequest;
use Modules\Blog\Models\Post;
use Modules\Blog\Transformers\PostResource;
use Modules\User\Enums\UserPermission;
use Symfony\Component\HttpFoundation\Response;

class ManagedPostController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Post::class);

        $posts = Post::query()
            ->when(
                ! $request->user()->can(UserPermission::PostsUpdateAny->value),
                fn ($query) => $query->whereBelongsTo($request->user(), 'author'),
            )
            ->when(
                $request->string('status')->isNotEmpty(),
                fn ($query) => $query->where('status', $request->string('status')->toString()),
            )
            ->with(['author.skills', 'author.media', 'translations', 'categories.translations', 'tags.translations', 'media'])
            ->withCount(['comments', 'likes', 'views'])
            ->latest()
            ->paginate(min($request->integer('per_page', 20), 100));

        return PostResource::collection($posts);
    }

    public function store(SavePostRequest $request, SavePost $save): JsonResponse
    {
        $post = $save->handle($request->user(), $this->data($request));

        return (new PostResource($post->load(['author.skills', 'author.media', 'media'])))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Post $managedPost): PostResource
    {
        Gate::authorize('view', $managedPost);

        return new PostResource($managedPost->load([
            'author.skills',
            'author.media',
            'translations',
            'categories.translations',
            'tags.translations',
            'media',
        ])->loadCount(['comments', 'likes', 'views']));
    }

    public function update(SavePostRequest $request, Post $managedPost, SavePost $save): PostResource
    {
        return new PostResource(
            $save->handle($request->user(), $this->data($request), $managedPost)
                ->load(['author.skills', 'author.media', 'media'])
                ->loadCount(['comments', 'likes', 'views']),
        );
    }

    public function destroy(Request $request, Post $managedPost): JsonResponse
    {
        Gate::authorize('delete', $managedPost);
        $managedPost->delete();

        return response()->json([], Response::HTTP_NO_CONTENT);
    }

    private function data(SavePostRequest $request): SavePostData
    {
        $validated = $request->validated();

        return new SavePostData(
            type: PostType::from($validated['type']),
            translations: $validated['translations'],
            categoryIds: $validated['category_ids'] ?? [],
            tagIds: $validated['tag_ids'] ?? [],
        );
    }
}
