<?php

namespace Modules\User\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\User\Enums\UserPermission;
use Modules\User\Enums\UserStatus;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $canViewPrivateProfile = $request->user()?->is($this->resource) === true
            || $request->user()?->can(UserPermission::UsersViewAny->value) === true;

        return [
            'id' => $this->getKey(),
            'username' => $this->username,
            'email' => $this->when($canViewPrivateProfile, $this->email),
            'phone' => $this->when($canViewPrivateProfile, $this->phone),
            'status' => $this->when(
                $canViewPrivateProfile,
                $this->status?->value ?? UserStatus::Active->value,
            ),
            'email_verified' => $this->when(
                $canViewPrivateProfile,
                $this->hasVerifiedEmail(),
            ),
            'phone_verified' => $this->when(
                $canViewPrivateProfile,
                $this->phone_verified_at !== null,
            ),
            'avatar' => $this->whenLoaded('media', fn (): ?array => $this->hasMedia('avatar') ? [
                'original' => $this->getFirstMediaUrl('avatar'),
                'small' => $this->getFirstMediaUrl('avatar', 'avatar_small'),
                'large' => $this->getFirstMediaUrl('avatar', 'avatar_large'),
            ] : null),
            'bio' => $this->bio,
            'about' => $this->about,
            'social_links' => $this->social_links ?? [],
            'preferred_locale' => $this->preferred_locale,
            'skills' => $this->whenLoaded(
                'skills',
                fn () => $this->skills->pluck('name')->values(),
            ),
            'roles' => $this->when(
                $canViewPrivateProfile,
                fn () => $this->getRoleNames(),
            ),
            'created_at' => $this->created_at,
        ];
    }
}
