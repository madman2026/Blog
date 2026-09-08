<?php

namespace Modules\User\Listeners;

use Modules\User\Events\AuthorApplicationReviewed;
use Modules\User\Notifications\AuthorApplicationReviewed as AuthorApplicationReviewedNotification;

class NotifyApplicantOfReview
{
    public function handle(AuthorApplicationReviewed $event): void
    {
        $event->application->user->notify(
            new AuthorApplicationReviewedNotification($event->application),
        );
    }
}
