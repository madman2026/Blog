<?php

namespace Modules\User\Listeners;

use Modules\User\Events\ManagedUserStatusChanged;
use Modules\User\Notifications\UserStatusChanged;

final class NotifyUserOfStatusChange
{
    public function handle(ManagedUserStatusChanged $event): void
    {
        $event->user->notify(new UserStatusChanged($event->status));
    }
}
