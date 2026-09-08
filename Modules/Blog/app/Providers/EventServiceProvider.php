<?php

namespace Modules\Blog\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Blog\Events\PostReviewed;
use Modules\Blog\Events\PostSubmittedForReview;
use Modules\Blog\Listeners\NotifyAuthorOfPostReview;
use Modules\Blog\Listeners\NotifyReviewersOfPostSubmission;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        PostSubmittedForReview::class => [NotifyReviewersOfPostSubmission::class],
        PostReviewed::class => [NotifyAuthorOfPostReview::class],
    ];

    /**
     * Configure the proper event listeners for email verification.
     */
    protected function configureEmailVerification(): void {}
}
