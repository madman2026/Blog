<?php

namespace Modules\Interaction\Actions;

use Illuminate\Database\Eloquent\Model;
use Modules\Interaction\Interfaces\Repositories\EngagementRepository;
use Modules\User\Models\User;

final readonly class ToggleLike
{
    public function __construct(private EngagementRepository $engagements) {}

    public function handle(User $user, Model $likeable): bool
    {
        return $this->engagements->toggleLike($user, $likeable);
    }
}
