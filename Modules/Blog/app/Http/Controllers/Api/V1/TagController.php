<?php

namespace Modules\Blog\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Modules\Blog\Http\Requests\Api\V1\SaveTagRequest;
use Modules\Blog\Models\Tag;
use Modules\Blog\Models\TagTranslation;
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

    public function store(SaveTagRequest $request): JsonResponse
    {
        $tag = $this->persist($request->validated());

        return (new TagResource($tag))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Tag $tag): TagResource
    {
        Gate::authorize(UserPermission::TaxonomiesManage->value);

        return new TagResource($tag->load('translations')->loadCount('posts'));
    }

    public function update(SaveTagRequest $request, Tag $tag): TagResource
    {
        return new TagResource($this->persist($request->validated(), $tag));
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

    /**
     * @param  array{translations: array<int, array{locale: string, name: string, slug: string}>}  $data
     */
    private function persist(array $data, ?Tag $tag = null): Tag
    {
        $this->ensureUniqueSlugs($data['translations'], $tag);

        return DB::transaction(function () use ($data, $tag): Tag {
            $tag ??= new Tag;
            $tag->save();

            $locales = collect($data['translations'])->pluck('locale');
            $tag->translations()->whereNotIn('locale', $locales)->delete();

            foreach ($data['translations'] as $translation) {
                $tag->translations()->updateOrCreate(
                    ['locale' => $translation['locale']],
                    [
                        'name' => $translation['name'],
                        'slug' => $translation['slug'],
                    ],
                );
            }

            return $tag->load('translations')->loadCount('posts');
        });
    }

    /**
     * @param  array<int, array{locale: string, slug: string}>  $translations
     */
    private function ensureUniqueSlugs(array $translations, ?Tag $tag): void
    {
        foreach ($translations as $index => $translation) {
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
    }
}
