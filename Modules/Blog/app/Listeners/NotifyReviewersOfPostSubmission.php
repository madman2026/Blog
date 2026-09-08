<?php

namespace Modules\Blog\Listeners;

use Illuminate\Support\Facades\Notification;
use Modules\Blog\Events\PostSubmittedForReview;
use Modules\Blog\Notifications\PostSubmittedForReview as PostSubmittedForReviewNotification;
use Modules\User\Enums\UserPermission;
use Modules\User\Models\User;

class NotifyReviewersOfPostSubmission
{
    public function handle(PostSubmittedForReview $event): void
    {
        User::query()
            ->permission(UserPermission::PostsReview->value)
            ->select(['id', 'email', 'preferred_locale'])
            ->chunkById(100, function ($reviewers) use ($event): void {
                Notification::send(
                    $reviewers,
                    new PostSubmittedForReviewNotification($event->post),
                );
            });
    }
}
