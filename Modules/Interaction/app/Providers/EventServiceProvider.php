<?php

namespace Modules\Interaction\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Interaction\Events\CommentModerated;
use Modules\Interaction\Events\CommentSubmitted;
use Modules\Interaction\Listeners\NotifyCommenterOfModeration;
use Modules\Interaction\Listeners\NotifyParticipantsOfApprovedComment;
use Modules\Interaction\Listeners\NotifyReviewersOfCommentSubmission;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        CommentSubmitted::class => [NotifyReviewersOfCommentSubmission::class],
        CommentModerated::class => [
            NotifyCommenterOfModeration::class,
            NotifyParticipantsOfApprovedComment::class,
        ],
    ];

    /**
     * Configure the proper event listeners for email verification.
     */
    protected function configureEmailVerification(): void {}
}
