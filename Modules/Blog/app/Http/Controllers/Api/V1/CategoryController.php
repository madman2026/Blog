<?php

namespace Modules\Blog\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Modules\Blog\Http\Requests\Api\V1\SaveCategoryRequest;
use Modules\Blog\Models\Category;
use Modules\Blog\Models\CategoryTranslation;
use Modules\Blog\Transformers\CategoryResource;
use Modules\User\Enums\UserPermission;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends Controller
{
    public function publicIndex(Request $request, string $locale): AnonymousResourceCollection
    {
        abort_unless(in_array($locale, config('platform.locales'), true), Response::HTTP_NOT_FOUND);

        $categories = Category::query()
            ->where('is_active', true)
            ->whereHas('translations', fn ($query) => $query->where('locale', $locale))
            ->with('translations')
            ->withCount(['children', 'posts'])
            ->orderBy('id')
            ->cursorPaginate(min($request->integer('per_page', 50), 100));

        return CategoryResource::collection($categories);
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize(UserPermission::TaxonomiesManage->value);

        $categories = Category::query()
            ->with('translations')
            ->withCount(['children', 'posts'])
            ->orderBy('id')
            ->paginate(min($request->integer('per_page', 20), 100));

        return CategoryResource::collection($categories);
    }

    public function store(SaveCategoryRequest $request): JsonResponse
    {
        $category = $this->persist($request->validated());

        return (new CategoryResource($category))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Category $category): CategoryResource
    {
        Gate::authorize(UserPermission::TaxonomiesManage->value);

        return new CategoryResource($category->load('translations')->loadCount(['children', 'posts']));
    }

    public function update(SaveCategoryRequest $request, Category $category): CategoryResource
    {
        if ($request->integer('parent_id') === $category->getKey()) {
            throw ValidationException::withMessages([
                'parent_id' => __('A category cannot be its own parent.'),
            ]);
        }

        return new CategoryResource($this->persist($request->validated(), $category));
    }

    public function destroy(Category $category): JsonResponse
    {
        Gate::authorize(UserPermission::TaxonomiesManage->value);

        if ($category->children()->exists() || $category->posts()->exists()) {
            throw ValidationException::withMessages([
                'category' => __('A category in use cannot be deleted.'),
            ]);
        }

        $category->delete();

        return response()->json([], Response::HTTP_NO_CONTENT);
    }

    /**
     * @param  array{parent_id?: int|null, is_active?: bool, translations: array<int, array{locale: string, name: string, slug: string, description?: string|null}>}  $data
     */
    private function persist(array $data, ?Category $category = null): Category
    {
        $this->ensureUniqueSlugs($data['translations'], $category);

        return DB::transaction(function () use ($data, $category): Category {
            $category ??= new Category;
            $category->fill([
                'parent_id' => $data['parent_id'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ])->save();

            $locales = collect($data['translations'])->pluck('locale');
            $category->translations()->whereNotIn('locale', $locales)->delete();

            foreach ($data['translations'] as $translation) {
                $category->translations()->updateOrCreate(
                    ['locale' => $translation['locale']],
                    [
                        'name' => $translation['name'],
                        'slug' => $translation['slug'],
                        'description' => $translation['description'] ?? null,
                    ],
                );
            }

            return $category->load('translations')->loadCount(['children', 'posts']);
        });
    }

    /**
     * @param  array<int, array{locale: string, slug: string}>  $translations
     */
    private function ensureUniqueSlugs(array $translations, ?Category $category): void
    {
        foreach ($translations as $index => $translation) {
            $exists = CategoryTranslation::query()
                ->where('locale', $translation['locale'])
                ->where('slug', $translation['slug'])
                ->when($category, fn ($query) => $query->where('category_id', '!=', $category->getKey()))
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    "translations.$index.slug" => __('This slug has already been taken.'),
                ]);
            }
        }
    }
}
