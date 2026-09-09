<?php

namespace Modules\User\Services;

use Modules\User\Enums\UserRole;
use Modules\User\Models\User;

final class ManagedUserPolicy
{
    public function canManage(User $actor, User $managedUser): bool
    {
        return ! $actor->is($managedUser);
    }

    public function canSuspend(User $managedUser): bool
    {
        return ! $managedUser->hasRole(UserRole::SuperUser->value);
    }
}
