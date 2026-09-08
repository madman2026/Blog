<?php

namespace Modules\Interaction\Actions;

use Illuminate\Database\Eloquent\Model;
use Modules\User\Models\User;

class ToggleLike
{
    public function handle(User $user, Model $likeable): bool
    {
        $like = $likeable->likes()->whereBelongsTo($user)->first();

        if ($like) {
            $like->delete();

            return false;
        }

        $likeable->likes()->create(['user_id' => $user->getKey()]);

        return true;
    }
}
