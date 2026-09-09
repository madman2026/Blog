<?php

namespace Modules\Interaction\Interfaces\Repositories;

use Illuminate\Database\Eloquent\Model;
use Modules\Interaction\Models\View;
use Modules\User\Models\User;

interface EngagementRepository
{
    public function toggleLike(User $user, Model $likeable): bool;

    public function toggleBookmark(User $user, Model $bookmarkable): bool;

    public function recordView(Model $viewable, string $visitorHash, ?int $userId): View;
}
