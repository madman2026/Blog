<?php

namespace Modules\User\Actions;

use Illuminate\Validation\ValidationException;
use Modules\User\Enums\UserRole;
use Modules\User\Models\User;

class UpdateManagedUserRole
{
    public function handle(User $managedUser, User $actor, UserRole $role): User
    {
        if ($actor->is($managedUser)) {
            throw ValidationException::withMessages(['role' => __('You cannot change your own role.')]);
        }

        $managedUser->syncRoles([$role->value]);

        return $managedUser->refresh();
    }
}
