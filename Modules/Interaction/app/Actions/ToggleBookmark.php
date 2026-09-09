<?php

namespace Modules\Interaction\Actions;

use Illuminate\Database\Eloquent\Model;
use Modules\Interaction\Interfaces\Repositories\EngagementRepository;
use Modules\User\Models\User;

final readonly class ToggleBookmark
{
    public function __construct(private EngagementRepository $engagements) {}

    public function handle(User $user, Model $bookmarkable): bool
    {
        return $this->engagements->toggleBookmark($user, $bookmarkable);
    }
}
