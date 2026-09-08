<?php

namespace Modules\Blog\Listeners;

use Modules\Blog\Events\PostReviewed;
use Modules\Blog\Notifications\PostReviewed as PostReviewedNotification;

class NotifyAuthorOfPostReview
{
    public function handle(PostReviewed $event): void
    {
        $event->post->author->notify(new PostReviewedNotification($event->post));
    }
}
