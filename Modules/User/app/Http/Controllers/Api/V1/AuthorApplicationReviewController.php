<?php

namespace Modules\User\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Modules\User\Actions\ReviewAuthorApplication;
use Modules\User\Enums\AuthorApplicationStatus;
use Modules\User\Http\Requests\Api\V1\ReviewAuthorApplicationRequest;
use Modules\User\Models\AuthorApplication;
use Modules\User\Transformers\AuthorApplicationResource;

class AuthorApplicationReviewController extends Controller
{
    public function __invoke(
        ReviewAuthorApplicationRequest $request,
        AuthorApplication $authorApplication,
        ReviewAuthorApplication $review,
    ): AuthorApplicationResource {
        $application = $review->handle(
            $authorApplication,
            $request->user(),
            AuthorApplicationStatus::from($request->validated('status')),
            $request->validated('notes'),
        );

        return new AuthorApplicationResource($application->load(['user.skills', 'reviewer']));
    }
}
