<?php

namespace Modules\User\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Modules\User\Http\Requests\Api\V1\UploadAvatarRequest;
use Modules\User\Transformers\UserResource;

class AvatarController extends Controller
{
    public function __invoke(UploadAvatarRequest $request): UserResource
    {
        $user = $request->user();

        $user
            ->addMediaFromRequest('avatar')
            ->toMediaCollection('avatar');

        return new UserResource($user->load(['skills', 'media']));
    }
}
