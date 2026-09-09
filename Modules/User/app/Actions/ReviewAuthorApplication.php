<?php

namespace Modules\User\Actions;

use Illuminate\Validation\ValidationException;
use Modules\User\Enums\AuthorApplicationStatus;
use Modules\User\Events\AuthorApplicationReviewed;
use Modules\User\Interfaces\Repositories\AuthorApplicationRepository;
use Modules\User\Models\AuthorApplication;
use Modules\User\Models\User;
use Modules\User\Services\AuthorApplicationWorkflow;

final readonly class ReviewAuthorApplication
{
    public function __construct(
        private AuthorApplicationWorkflow $workflow,
        private AuthorApplicationRepository $applications,
    ) {}

    public function handle(
        AuthorApplication $application,
        User $reviewer,
        AuthorApplicationStatus $decision,
        ?string $notes = null,
    ): AuthorApplication {
        if (! $this->workflow->isReviewDecision($decision)) {
            throw ValidationException::withMessages([
                'status' => __('A review must approve or reject the application.'),
            ]);
        }

        if (! $this->workflow->canReview($application->status)) {
            throw ValidationException::withMessages([
                'application' => __('Only pending applications may be reviewed.'),
            ]);
        }

        $application = $this->applications->review($application, $reviewer, $decision, $notes);

        AuthorApplicationReviewed::dispatch($application);

        return $application;
    }
}
