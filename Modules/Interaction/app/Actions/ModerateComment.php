<?php

namespace Modules\Interaction\Actions;

use Illuminate\Validation\ValidationException;
use Modules\Interaction\Enums\CommentStatus;
use Modules\Interaction\Events\CommentModerated;
use Modules\Interaction\Interfaces\Repositories\CommentRepository;
use Modules\Interaction\Models\Comment;
use Modules\Interaction\Services\CommentWorkflow;
use Modules\User\Models\User;

final readonly class ModerateComment
{
    public function __construct(
        private CommentWorkflow $workflow,
        private CommentRepository $comments,
    ) {}

    public function handle(
        Comment $comment,
        User $moderator,
        CommentStatus $status,
        ?string $notes = null,
    ): Comment {
        if (! $this->workflow->isModerationDecision($status)) {
            throw ValidationException::withMessages([
                'status' => __('A moderation decision must approve or reject the comment.'),
            ]);
        }

        $comment = $this->comments->moderate($comment, $moderator, $status, $notes);

        CommentModerated::dispatch($comment);

        return $comment;
    }
}
