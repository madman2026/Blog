<?php

namespace Modules\User\Actions;

use Illuminate\Validation\ValidationException;
use Modules\User\Enums\UserRole;
use Modules\User\Enums\UserStatus;
use Modules\User\Models\User;

class UpdateManagedUserStatus
{
    public function handle(User $managedUser, User $actor, UserStatus $status): User
    {
        if ($actor->is($managedUser)) {
            throw ValidationException::withMessages(['status' => __('You cannot change the status of your own account.')]);
        }

        if ($managedUser->hasRole(UserRole::SuperUser->value)) {
            throw ValidationException::withMessages(['status' => __('A super-user account cannot be suspended.')]);
        }

        $managedUser->update(['status' => $status]);

        if ($status === UserStatus::Suspended) {
            $managedUser->tokens()->delete();
        }

        return $managedUser->refresh();
    }
}
