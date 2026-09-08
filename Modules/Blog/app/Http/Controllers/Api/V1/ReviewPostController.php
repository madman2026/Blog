<?php

namespace Modules\Blog\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Modules\Blog\Actions\ReviewPost;
use Modules\Blog\Enums\ReviewDecision;
use Modules\Blog\Http\Requests\Api\V1\ReviewPostRequest;
use Modules\Blog\Models\Post;
use Modules\Blog\Transformers\PostResource;

class ReviewPostController extends Controller
{
    public function __invoke(ReviewPostRequest $request, Post $managedPost, ReviewPost $review): PostResource
    {
        $post = $review->handle(
            $managedPost,
            $request->user(),
            ReviewDecision::from($request->validated('decision')),
            $request->validated('notes'),
        );

        return new PostResource($post->load(['author.skills', 'author.media', 'translations', 'media']));
    }
}
