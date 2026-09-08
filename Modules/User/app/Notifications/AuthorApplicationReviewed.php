<?php

namespace Modules\User\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\User\Models\AuthorApplication;

class AuthorApplicationReviewed extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public AuthorApplication $application)
    {
        $this->afterCommit();
    }

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast', 'mail'];
    }

    /** @return array<string, string> */
    public function viaQueues(): array
    {
        return [
            'database' => 'notifications',
            'broadcast' => 'notifications',
            'mail' => 'notifications',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Your author application was reviewed'))
            ->line(__('Your author application status is now :status.', [
                'status' => $this->application->status->value,
            ]))
            ->action(__('View profile'), url('/user/settings'));
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'author-application-reviewed',
            'application_id' => $this->application->getKey(),
            'status' => $this->application->status->value,
            'message' => __('Your author application was reviewed.'),
        ];
    }
}
