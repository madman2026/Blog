<?php

namespace Modules\Interaction\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Modules\Blog\Models\PostTranslation;
use Modules\Interaction\Actions\ToggleBookmark;

class ToggleBookmarkController extends Controller
{
    public function __invoke(
        Request $request,
        string $locale,
        PostTranslation $postTranslation,
        ToggleBookmark $toggle,
    ): JsonResponse {
        abort_unless($postTranslation->locale === $locale, 404);
        $post = $postTranslation->post;
        Gate::authorize('view', $post);

        return response()->json([
            'bookmarked' => $toggle->handle($request->user(), $post),
        ]);
    }
}
