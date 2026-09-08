<?php

namespace Modules\User\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\User\Events\AuthorApplicationReviewed;
use Modules\User\Events\AuthorApplicationSubmitted;
use Modules\User\Listeners\NotifyAdminsOfAuthorApplication;
use Modules\User\Listeners\NotifyApplicantOfReview;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        AuthorApplicationSubmitted::class => [NotifyAdminsOfAuthorApplication::class],
        AuthorApplicationReviewed::class => [NotifyApplicantOfReview::class],
    ];

    /**
     * Configure the proper event listeners for email verification.
     */
    protected function configureEmailVerification(): void {}
}
