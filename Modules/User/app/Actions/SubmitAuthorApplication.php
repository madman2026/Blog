<?php

namespace Modules\User\Actions;

use Illuminate\Validation\ValidationException;
use Modules\User\Enums\AuthorApplicationStatus;
use Modules\User\Enums\UserRole;
use Modules\User\Events\AuthorApplicationSubmitted;
use Modules\User\Models\AuthorApplication;
use Modules\User\Models\User;

class SubmitAuthorApplication
{
    public function handle(User $user): AuthorApplication
    {
        if ($user->hasRole(UserRole::Author->value)) {
            throw ValidationException::withMessages([
                'application' => __('You are already an author.'),
            ]);
        }

        $missing = collect([
            'avatar' => $user->hasMedia('avatar'),
            'bio' => $user->bio,
            'about' => $user->about,
            'social_links' => $user->social_links,
            'skills' => $user->skills()->exists(),
        ])->filter(fn (mixed $value): bool => blank($value))->keys();

        if ($missing->isNotEmpty()) {
            throw ValidationException::withMessages([
                'profile' => __('Complete your profile before applying: :fields', [
                    'fields' => $missing->implode(', '),
                ]),
            ]);
        }

        $application = AuthorApplication::query()->updateOrCreate(
            ['user_id' => $user->getKey()],
            [
                'status' => AuthorApplicationStatus::Pending,
                'reviewed_by' => null,
                'reviewed_at' => null,
                'review_notes' => null,
            ],
        );

        AuthorApplicationSubmitted::dispatch($application);

        return $application;
    }
}
