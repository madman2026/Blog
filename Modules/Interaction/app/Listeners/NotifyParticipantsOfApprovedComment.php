<?php

namespace Modules\Interaction\Listeners;

use Illuminate\Support\Facades\Notification;
use Modules\Blog\Models\Post;
use Modules\Interaction\Enums\CommentStatus;
use Modules\Interaction\Events\CommentModerated;
use Modules\Interaction\Notifications\ApprovedCommentAdded;

class NotifyParticipantsOfApprovedComment
{
    public function handle(CommentModerated $event): void
    {
        if ($event->comment->status !== CommentStatus::Approved) {
            return;
        }

        $comment = $event->comment->load([
            'user',
            'parent.user',
            'commentable.author',
            'commentable.translations',
        ]);

        if (! ($comment->commentable instanceof Post)) {
            return;
        }

        $recipients = collect([
            $comment->commentable->author,
            $comment->parent?->user,
        ])->filter(
            fn ($user): bool => $user !== null && $user->getKey() !== $comment->user_id,
        )->unique(fn ($user) => $user->getKey());

        Notification::send($recipients, new ApprovedCommentAdded($comment));
    }
}
