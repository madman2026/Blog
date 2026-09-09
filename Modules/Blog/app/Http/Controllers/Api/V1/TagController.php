<?php

namespace Modules\Blog\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Modules\Blog\Actions\SaveTag;
use Modules\Blog\Http\Requests\Api\V1\SaveTagRequest;
use Modules\Blog\Models\Tag;
use Modules\Blog\Transformers\TagResource;
use Modules\User\Enums\UserPermission;
use Symfony\Component\HttpFoundation\Response;

class TagController extends Controller
{
    public function publicIndex(Request $request, string $locale): AnonymousResourceCollection
    {
        abort_unless(in_array($locale, config('platform.locales'), true), Response::HTTP_NOT_FOUND);

        $tags = Tag::query()
            ->whereHas('translations', fn ($query) => $query->where('locale', $locale))
            ->with('translations')
            ->withCount('posts')
            ->orderBy('id')
            ->cursorPaginate(min($request->integer('per_page', 50), 100));

        return TagResource::collection($tags);
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize(UserPermission::TaxonomiesManage->value);

        $tags = Tag::query()
            ->with('translations')
            ->withCount('posts')
            ->orderBy('id')
            ->paginate(min($request->integer('per_page', 20), 100));

        return TagResource::collection($tags);
    }

    public function store(SaveTagRequest $request, SaveTag $saveTag): JsonResponse
    {
        $tag = $saveTag->handle($request->validated());

        return (new TagResource($tag))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Tag $tag): TagResource
    {
        Gate::authorize(UserPermission::TaxonomiesManage->value);

        return new TagResource($tag->load('translations')->loadCount('posts'));
    }

    public function update(SaveTagRequest $request, Tag $tag, SaveTag $saveTag): TagResource
    {
        return new TagResource($saveTag->handle($request->validated(), $tag));
    }

    public function destroy(Tag $tag): JsonResponse
    {
        Gate::authorize(UserPermission::TaxonomiesManage->value);

        if ($tag->posts()->exists()) {
            throw ValidationException::withMessages([
                'tag' => __('A tag in use cannot be deleted.'),
            ]);
        }

        $tag->delete();

        return response()->json([], Response::HTTP_NO_CONTENT);
    }
}
