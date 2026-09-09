<?php

namespace Modules\Blog\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Modules\Blog\Actions\SaveCategory;
use Modules\Blog\Http\Requests\Api\V1\SaveCategoryRequest;
use Modules\Blog\Models\Category;
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

    public function store(SaveCategoryRequest $request, SaveCategory $saveCategory): JsonResponse
    {
        $category = $saveCategory->handle($request->validated());

        return (new CategoryResource($category))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Category $category): CategoryResource
    {
        Gate::authorize(UserPermission::TaxonomiesManage->value);

        return new CategoryResource($category->load('translations')->loadCount(['children', 'posts']));
    }

    public function update(SaveCategoryRequest $request, Category $category, SaveCategory $saveCategory): CategoryResource
    {
        return new CategoryResource($saveCategory->handle($request->validated(), $category));
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
}
