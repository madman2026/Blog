<?php

namespace Modules\Blog\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Modules\Blog\Actions\SubmitPostForReview;
use Modules\Blog\Models\Post;
use Modules\Blog\Transformers\PostResource;

class SubmitPostController extends Controller
{
    public function __invoke(Request $request, Post $managedPost, SubmitPostForReview $submit): PostResource
    {
        Gate::authorize('submit', $managedPost);

        return new PostResource($submit->handle($managedPost)->load(['author.skills', 'author.media', 'translations', 'media']));
    }
}
