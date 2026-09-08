<?php

namespace Modules\User\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\User\Enums\AuthorApplicationStatus;
use Modules\User\Enums\UserRole;
use Modules\User\Events\AuthorApplicationReviewed;
use Modules\User\Models\AuthorApplication;
use Modules\User\Models\User;

class ReviewAuthorApplication
{
    public function handle(
        AuthorApplication $application,
        User $reviewer,
        AuthorApplicationStatus $decision,
        ?string $notes = null,
    ): AuthorApplication {
        if ($decision === AuthorApplicationStatus::Pending) {
            throw ValidationException::withMessages([
                'status' => __('A review must approve or reject the application.'),
            ]);
        }

        if ($application->status !== AuthorApplicationStatus::Pending) {
            throw ValidationException::withMessages([
                'application' => __('Only pending applications may be reviewed.'),
            ]);
        }

        $application = DB::transaction(function () use ($application, $reviewer, $decision, $notes): AuthorApplication {
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

        AuthorApplicationReviewed::dispatch($application);

        return $application;
    }
}
