<?php

namespace Modules\User\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\User\Enums\UserStatus;

final class UserStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public UserStatus $status)
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
        return ['database' => 'notifications', 'broadcast' => 'notifications', 'mail' => 'notifications'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Your account status changed'))
            ->line(__('Your account status is now :status.', ['status' => $this->status->value]));
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'user-status-changed',
            'status' => $this->status->value,
            'message' => __('Your account status changed.'),
        ];
    }
}
