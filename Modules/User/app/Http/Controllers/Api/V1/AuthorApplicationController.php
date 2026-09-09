<?php

namespace Modules\User\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Modules\User\Actions\SubmitAuthorApplication;
use Modules\User\Models\AuthorApplication;
use Modules\User\Queries\AuthorApplications;
use Modules\User\Transformers\AuthorApplicationResource;

class AuthorApplicationController extends Controller
{
    public function index(Request $request, AuthorApplications $authorApplications): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', AuthorApplication::class);

        $applications = $authorApplications->paginate(
            $request->string('status')->toString(),
            min($request->integer('per_page', 20), 100),
        );

        return AuthorApplicationResource::collection($applications);
    }

    public function store(Request $request, SubmitAuthorApplication $submit): AuthorApplicationResource
    {
        Gate::authorize('create', AuthorApplication::class);

        return new AuthorApplicationResource($submit->handle($request->user())->load('user.skills'));
    }

    public function current(Request $request): AuthorApplicationResource
    {
        $application = $request->user()->authorApplication()->firstOrFail();
        Gate::authorize('view', $application);

        return new AuthorApplicationResource($application->load(['user.skills', 'reviewer']));
    }
}
