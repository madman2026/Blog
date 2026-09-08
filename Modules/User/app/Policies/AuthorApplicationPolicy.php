<?php

namespace Modules\User\Policies;

use Modules\User\Enums\AuthorApplicationStatus;
use Modules\User\Enums\UserPermission;
use Modules\User\Enums\UserRole;
use Modules\User\Models\AuthorApplication;
use Modules\User\Models\User;

class AuthorApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(UserPermission::AuthorApplicationsReview->value);
    }

    public function view(User $user, AuthorApplication $application): bool
    {
        return $application->user_id === $user->getKey()
            || $user->can(UserPermission::AuthorApplicationsReview->value);
    }

    public function create(User $user): bool
    {
        return ! $user->hasRole(UserRole::Author->value);
    }

    public function review(User $user, AuthorApplication $application): bool
    {
        return $application->status === AuthorApplicationStatus::Pending
            && $user->can(UserPermission::AuthorApplicationsReview->value);
    }
}
