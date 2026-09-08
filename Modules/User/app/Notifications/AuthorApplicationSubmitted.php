<?php

namespace Modules\User\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Modules\User\Models\AuthorApplication;

class AuthorApplicationSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public AuthorApplication $application)
    {
        $this->afterCommit();
    }

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /** @return array<string, string> */
    public function viaQueues(): array
    {
        return ['database' => 'notifications', 'broadcast' => 'notifications'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'author-application-submitted',
            'application_id' => $this->application->getKey(),
            'user_id' => $this->application->user_id,
            'message' => __('A new author application is ready for review.'),
        ];
    }
}
