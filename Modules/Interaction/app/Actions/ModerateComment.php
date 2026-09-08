<?php

namespace Modules\Interaction\Actions;

use Illuminate\Validation\ValidationException;
use Modules\Interaction\Enums\CommentStatus;
use Modules\Interaction\Events\CommentModerated;
use Modules\Interaction\Models\Comment;
use Modules\User\Models\User;

class ModerateComment
{
    public function handle(
        Comment $comment,
        User $moderator,
        CommentStatus $status,
        ?string $notes = null,
    ): Comment {
        if ($status === CommentStatus::Pending) {
            throw ValidationException::withMessages([
                'status' => __('A moderation decision must approve or reject the comment.'),
            ]);
        }

        $comment->update([
            'status' => $status,
            'moderated_by' => $moderator->getKey(),
            'moderated_at' => now(),
            'moderation_notes' => $notes,
        ]);

        CommentModerated::dispatch($comment);

        return $comment->refresh();
    }
}
