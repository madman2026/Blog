<?php

namespace Modules\Interaction\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Modules\Interaction\Models\Comment;
use Modules\Interaction\Queries\PendingComments;
use Modules\Interaction\Transformers\CommentResource;

class PendingCommentController extends Controller
{
    public function __invoke(Request $request, PendingComments $pendingComments): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Comment::class);

        return CommentResource::collection(
            $pendingComments->paginate(min($request->integer('per_page', 20), 100)),
        );
    }
}
