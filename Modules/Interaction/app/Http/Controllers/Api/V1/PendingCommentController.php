<?php

namespace Modules\Interaction\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Modules\Interaction\Enums\CommentStatus;
use Modules\Interaction\Models\Comment;
use Modules\Interaction\Transformers\CommentResource;

class PendingCommentController extends Controller
{
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Comment::class);

        return CommentResource::collection(
            Comment::query()
                ->where('status', CommentStatus::Pending)
                ->with(['user.skills', 'commentable'])
                ->oldest()
                ->paginate(min($request->integer('per_page', 20), 100)),
        );
    }
}
