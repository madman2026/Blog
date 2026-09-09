<?php

namespace Modules\User\Interfaces\Repositories;

use Modules\User\Data\UpdateProfileData;
use Modules\User\Enums\UserRole;
use Modules\User\Enums\UserStatus;
use Modules\User\Models\User;

interface UserRepository
{
    /** @param array<string, mixed> $attributes */
    public function register(array $attributes): User;

    public function findForAuthentication(string $field, string $identifier): ?User;

    public function updateProfile(User $user, UpdateProfileData $data): User;

    public function updatePassword(User $user, string $password): void;

    public function resetPassword(User $user, string $password): void;

    public function updateRole(User $user, UserRole $role): User;

    public function updateStatus(User $user, UserStatus $status): User;
}
