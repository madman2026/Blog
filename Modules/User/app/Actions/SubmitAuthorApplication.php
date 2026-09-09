<?php

namespace Modules\User\Actions;

use Illuminate\Validation\ValidationException;
use Modules\User\Events\AuthorApplicationSubmitted;
use Modules\User\Interfaces\Repositories\AuthorApplicationRepository;
use Modules\User\Models\AuthorApplication;
use Modules\User\Models\User;
use Modules\User\Services\AuthorApplicationWorkflow;

final readonly class SubmitAuthorApplication
{
    public function __construct(
        private AuthorApplicationWorkflow $workflow,
        private AuthorApplicationRepository $applications,
    ) {}

    public function handle(User $user): AuthorApplication
    {
        if ($this->workflow->isAuthor($user)) {
            throw ValidationException::withMessages([
                'application' => __('You are already an author.'),
            ]);
        }

        $missing = $this->workflow->missingProfileFields($user);

        if ($missing->isNotEmpty()) {
            throw ValidationException::withMessages([
                'profile' => __('Complete your profile before applying: :fields', [
                    'fields' => $missing->implode(', '),
                ]),
            ]);
        }

        $application = $this->applications->submit($user);

        AuthorApplicationSubmitted::dispatch($application);

        return $application;
    }
}
