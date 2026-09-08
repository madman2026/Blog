<?php

namespace Modules\User\Listeners;

use Illuminate\Support\Facades\Notification;
use Modules\User\Enums\UserPermission;
use Modules\User\Events\AuthorApplicationSubmitted;
use Modules\User\Models\User;
use Modules\User\Notifications\AuthorApplicationSubmitted as AuthorApplicationSubmittedNotification;

class NotifyAdminsOfAuthorApplication
{
    public function handle(AuthorApplicationSubmitted $event): void
    {
        User::query()
            ->permission(UserPermission::AuthorApplicationsReview->value)
            ->select(['id', 'email', 'preferred_locale'])
            ->chunkById(100, function ($reviewers) use ($event): void {
                Notification::send(
                    $reviewers,
                    new AuthorApplicationSubmittedNotification($event->application),
                );
            });
    }
}
