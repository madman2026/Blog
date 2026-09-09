<?php

namespace Modules\User\Actions;

use Modules\User\Data\UpdateProfileData;
use Modules\User\Interfaces\Repositories\UserRepository;
use Modules\User\Models\User;
use Modules\User\Services\UserProfile;

final readonly class UpdateProfile
{
    public function __construct(
        private UserProfile $profile,
        private UserRepository $users,
    ) {}

    public function handle(User $user, UpdateProfileData $data): User
    {
        $attributes = $data->attributes;

        if (array_key_exists('social_links', $attributes)) {
            $attributes['social_links'] = $this->profile->normalizeSocialLinks($attributes['social_links'] ?? []);
        }

        $skills = $data->skills === null
            ? null
            : $this->profile->normalizeSkills($data->skills);

        return $this->users->updateProfile(
            $user,
            new UpdateProfileData($attributes, $skills),
        );
    }
}
