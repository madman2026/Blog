<?php

namespace Modules\Interaction\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Modules\Interaction\Actions\ModerateComment;
use Modules\Interaction\Enums\CommentStatus;
use Modules\Interaction\Http\Requests\Api\V1\ModerateCommentRequest;
use Modules\Interaction\Models\Comment;
use Modules\Interaction\Transformers\CommentResource;

class CommentModerationController extends Controller
{
    public function __invoke(
        ModerateCommentRequest $request,
        Comment $comment,
        ModerateComment $moderate,
    ): CommentResource {
        $comment = $moderate->handle(
            $comment,
            $request->user(),
            CommentStatus::from($request->validated('status')),
            $request->validated('notes'),
        );

        return new CommentResource($comment->load('user.skills'));
    }
}
