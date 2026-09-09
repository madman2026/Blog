<?php

namespace Modules\User\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Modules\User\Actions\UploadAvatar;
use Modules\User\Http\Requests\Api\V1\UploadAvatarRequest;
use Modules\User\Transformers\UserResource;

class AvatarController extends Controller
{
    public function __invoke(UploadAvatarRequest $request, UploadAvatar $uploadAvatar): UserResource
    {
        return new UserResource($uploadAvatar->handle(
            $request->user(),
            $request->file('avatar'),
        ));
    }
}
