<?php

namespace Modules\User\Repositories;

use Illuminate\Support\Facades\DB;
use Modules\User\Enums\AuthorApplicationStatus;
use Modules\User\Enums\UserRole;
use Modules\User\Interfaces\Repositories\AuthorApplicationRepository;
use Modules\User\Models\AuthorApplication;
use Modules\User\Models\User;

final class EloquentAuthorApplicationRepository implements AuthorApplicationRepository
{
    public function submit(User $user): AuthorApplication
    {
        return AuthorApplication::query()->updateOrCreate(
            ['user_id' => $user->getKey()],
            [
                'status' => AuthorApplicationStatus::Pending,
                'reviewed_by' => null,
                'reviewed_at' => null,
                'review_notes' => null,
            ],
        );
    }

    public function review(
        AuthorApplication $application,
        User $reviewer,
        AuthorApplicationStatus $decision,
        ?string $notes,
    ): AuthorApplication {
        return DB::transaction(function () use ($application, $reviewer, $decision, $notes): AuthorApplication {
            $application->update([
                'status' => $decision,
                'reviewed_by' => $reviewer->getKey(),
                'reviewed_at' => now(),
                'review_notes' => $notes,
            ]);

            if ($decision === AuthorApplicationStatus::Approved) {
                $application->user->assignRole(UserRole::Author->value);
            }

            return $application->refresh();
        });
    }
}
