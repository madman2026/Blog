<?php

namespace Modules\User\Services;

use Illuminate\Support\Collection;
use Modules\User\Enums\AuthorApplicationStatus;
use Modules\User\Enums\UserRole;
use Modules\User\Models\User;

final class AuthorApplicationWorkflow
{
    public function isAuthor(User $user): bool
    {
        return $user->hasRole(UserRole::Author->value);
    }

    /** @return Collection<int, string> */
    public function missingProfileFields(User $user): Collection
    {
        return collect([
            'avatar' => $user->hasMedia('avatar'),
            'bio' => $user->bio,
            'about' => $user->about,
            'social_links' => $user->social_links,
            'skills' => $user->skills()->exists(),
        ])->filter(fn (mixed $value): bool => blank($value))->keys();
    }

    public function isReviewDecision(AuthorApplicationStatus $decision): bool
    {
        return $decision !== AuthorApplicationStatus::Pending;
    }

    public function canReview(AuthorApplicationStatus $status): bool
    {
        return $status === AuthorApplicationStatus::Pending;
    }
}
