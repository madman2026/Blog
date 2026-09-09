<?php

namespace Modules\User\Listeners;

use Modules\User\Events\ManagedUserRoleChanged;
use Modules\User\Notifications\UserRoleChanged;

final class NotifyUserOfRoleChange
{
    public function handle(ManagedUserRoleChanged $event): void
    {
        $event->user->notify(new UserRoleChanged($event->role));
    }
}
