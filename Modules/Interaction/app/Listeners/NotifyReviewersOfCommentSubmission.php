<?php

namespace Modules\Interaction\Listeners;

use Illuminate\Support\Facades\Notification;
use Modules\Interaction\Events\CommentSubmitted;
use Modules\Interaction\Notifications\CommentSubmitted as CommentSubmittedNotification;
use Modules\User\Enums\UserPermission;
use Modules\User\Models\User;

class NotifyReviewersOfCommentSubmission
{
    public function handle(CommentSubmitted $event): void
    {
        User::query()
            ->permission(UserPermission::CommentsModerate->value)
            ->select(['id', 'email', 'preferred_locale'])
            ->chunkById(100, function ($moderators) use ($event): void {
                Notification::send(
                    $moderators,
                    new CommentSubmittedNotification($event->comment),
                );
            });
    }
}
