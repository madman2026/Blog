<?php

namespace Modules\Interaction\Actions;

use Illuminate\Database\Eloquent\Model;
use Modules\User\Models\User;

class ToggleBookmark
{
    public function handle(User $user, Model $bookmarkable): bool
    {
        $bookmark = $bookmarkable->bookmarks()->whereBelongsTo($user)->first();

        if ($bookmark) {
            $bookmark->delete();

            return false;
        }

        $bookmarkable->bookmarks()->create(['user_id' => $user->getKey()]);

        return true;
    }
}
