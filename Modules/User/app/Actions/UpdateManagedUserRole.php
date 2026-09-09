<?php

namespace Modules\User\Actions;

use Illuminate\Validation\ValidationException;
use Modules\User\Enums\UserRole;
use Modules\User\Events\ManagedUserRoleChanged;
use Modules\User\Interfaces\Repositories\UserRepository;
use Modules\User\Models\User;
use Modules\User\Services\ManagedUserPolicy;

final readonly class UpdateManagedUserRole
{
    public function __construct(
        private ManagedUserPolicy $policy,
        private UserRepository $users,
    ) {}

    public function handle(User $managedUser, User $actor, UserRole $role): User
    {
        if (! $this->policy->canManage($actor, $managedUser)) {
            throw ValidationException::withMessages(['role' => __('You cannot change your own role.')]);
        }

        $managedUser = $this->users->updateRole($managedUser, $role);
        ManagedUserRoleChanged::dispatch($managedUser, $role);

        return $managedUser;
    }
}
