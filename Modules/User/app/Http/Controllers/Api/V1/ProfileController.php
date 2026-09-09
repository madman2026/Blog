<?php

namespace Modules\User\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\User\Actions\UpdateProfile;
use Modules\User\Data\UpdateProfileData;
use Modules\User\Http\Requests\Api\V1\UpdateProfileRequest;
use Modules\User\Transformers\UserResource;

class ProfileController extends Controller
{
    public function show(Request $request): UserResource
    {
        return new UserResource($request->user()->load(['skills', 'media']));
    }

    public function update(UpdateProfileRequest $request, UpdateProfile $updateProfile): UserResource
    {
        $validated = $request->validated();
        $user = $updateProfile->handle($request->user(), new UpdateProfileData(
            attributes: collect($validated)->except('skills')->all(),
            skills: array_key_exists('skills', $validated) ? $validated['skills'] : null,
        ));

        return new UserResource($user);
    }
}
