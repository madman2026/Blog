<?php

namespace Modules\Interaction\Repositories;

use Illuminate\Database\Eloquent\Model;
use Modules\Interaction\Interfaces\Repositories\EngagementRepository;
use Modules\Interaction\Models\View;
use Modules\User\Models\User;

final class EloquentEngagementRepository implements EngagementRepository
{
    public function toggleLike(User $user, Model $likeable): bool
    {
        $like = $likeable->likes()->whereBelongsTo($user)->first();

        if ($like) {
            $like->delete();

            return false;
        }

        $likeable->likes()->create(['user_id' => $user->getKey()]);

        return true;
    }

    public function toggleBookmark(User $user, Model $bookmarkable): bool
    {
        $bookmark = $bookmarkable->bookmarks()->whereBelongsTo($user)->first();

        if ($bookmark) {
            $bookmark->delete();

            return false;
        }

        $bookmarkable->bookmarks()->create(['user_id' => $user->getKey()]);

        return true;
    }

    public function recordView(Model $viewable, string $visitorHash, ?int $userId): View
    {
        return $viewable->views()->firstOrCreate(
            [
                'visitor_hash' => $visitorHash,
                'viewed_on' => today(),
            ],
            ['user_id' => $userId],
        );
    }
}
