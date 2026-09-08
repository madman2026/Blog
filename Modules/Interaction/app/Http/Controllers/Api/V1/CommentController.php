<?php

namespace Modules\Interaction\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Modules\Blog\Models\PostTranslation;
use Modules\Interaction\Actions\SubmitComment;
use Modules\Interaction\Http\Requests\Api\V1\StoreCommentRequest;
use Modules\Interaction\Models\Comment;
use Modules\Interaction\Transformers\CommentResource;

class CommentController extends Controller
{
    public function index(Request $request, string $locale, PostTranslation $postTranslation): AnonymousResourceCollection
    {
        abort_unless($postTranslation->locale === $locale, 404);
        $post = $postTranslation->post;
        Gate::authorize('view', $post);

        $comments = $post->comments()
            ->approved()
            ->whereNull('parent_id')
            ->with([
                'user.skills',
                'replies' => fn ($query) => $query->approved()->with('user.skills')->oldest(),
            ])
            ->oldest()
            ->paginate(min($request->integer('per_page', 20), 100));

        return CommentResource::collection($comments);
    }

    public function store(
        StoreCommentRequest $request,
        string $locale,
        PostTranslation $postTranslation,
        SubmitComment $submit,
    ): CommentResource {
        abort_unless($postTranslation->locale === $locale, 404);
        $post = $postTranslation->post;
        Gate::authorize('view', $post);

        $parent = $request->filled('parent_id')
            ? Comment::query()->findOrFail($request->integer('parent_id'))
            : null;

        $comment = $submit->handle(
            $request->user(),
            $post,
            $request->validated('body'),
            $parent,
        );

        return new CommentResource($comment->load('user.skills'));
    }
}
