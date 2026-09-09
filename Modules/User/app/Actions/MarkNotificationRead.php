<?php

namespace Modules\User\Actions;

use Illuminate\Notifications\DatabaseNotification;
use Modules\User\Models\User;

final class MarkNotificationRead
{
    public function handle(User $user, string $notificationId): DatabaseNotification
    {
        $notification = $user->notifications()->findOrFail($notificationId);
        $notification->markAsRead();

        return $notification->refresh();
    }
}
