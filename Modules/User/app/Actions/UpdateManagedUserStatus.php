<?php

namespace Modules\User\Actions;

use Illuminate\Validation\ValidationException;
use Modules\User\Enums\UserStatus;
use Modules\User\Events\ManagedUserStatusChanged;
use Modules\User\Interfaces\Repositories\UserRepository;
use Modules\User\Models\User;
use Modules\User\Services\ManagedUserPolicy;

final readonly class UpdateManagedUserStatus
{
    public function __construct(
        private ManagedUserPolicy $policy,
        private UserRepository $users,
    ) {}

    public function handle(User $managedUser, User $actor, UserStatus $status): User
    {
        if (! $this->policy->canManage($actor, $managedUser)) {
            throw ValidationException::withMessages(['status' => __('You cannot change the status of your own account.')]);
        }

        if (! $this->policy->canSuspend($managedUser)) {
            throw ValidationException::withMessages(['status' => __('A super-user account cannot be suspended.')]);
        }

        $managedUser = $this->users->updateStatus($managedUser, $status);
        ManagedUserStatusChanged::dispatch($managedUser, $status);

        return $managedUser;
    }
}
