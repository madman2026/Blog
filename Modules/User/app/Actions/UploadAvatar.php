<?php

namespace Modules\User\Actions;

use Modules\User\Models\User;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class UploadAvatar
{
    public function handle(User $user, UploadedFile $avatar): User
    {
        $user->addMedia($avatar)->toMediaCollection('avatar');

        return $user->refresh()->load(['skills', 'media']);
    }
}
