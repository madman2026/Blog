<?php

namespace Modules\User\Actions;

use Modules\User\Models\User;

final class MarkAllNotificationsRead
{
    public function handle(User $user): void
    {
        $user->unreadNotifications()->update(['read_at' => now()]);
    }
}
