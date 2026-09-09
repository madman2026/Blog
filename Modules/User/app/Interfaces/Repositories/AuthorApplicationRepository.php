<?php

namespace Modules\User\Interfaces\Repositories;

use Modules\User\Enums\AuthorApplicationStatus;
use Modules\User\Models\AuthorApplication;
use Modules\User\Models\User;

interface AuthorApplicationRepository
{
    public function submit(User $user): AuthorApplication;

    public function review(
        AuthorApplication $application,
        User $reviewer,
        AuthorApplicationStatus $decision,
        ?string $notes,
    ): AuthorApplication;
}
