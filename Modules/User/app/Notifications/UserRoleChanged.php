<?php

namespace Modules\User\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\User\Enums\UserRole;

final class UserRoleChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public UserRole $role)
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
            ->subject(__('Your account role changed'))
            ->line(__('Your account role is now :role.', ['role' => $this->role->value]))
            ->action(__('Open your account'), url('/user/settings'));
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'user-role-changed',
            'role' => $this->role->value,
            'message' => __('Your account role changed.'),
        ];
    }
}
