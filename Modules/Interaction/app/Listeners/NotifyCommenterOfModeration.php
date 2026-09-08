<?php

namespace Modules\Interaction\Listeners;

use Modules\Interaction\Events\CommentModerated;
use Modules\Interaction\Notifications\CommentModerated as CommentModeratedNotification;

class NotifyCommenterOfModeration
{
    public function handle(CommentModerated $event): void
    {
        $event->comment->user->notify(new CommentModeratedNotification($event->comment));
    }
}
