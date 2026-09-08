<?php

use Illuminate\Support\Facades\Broadcast;
use Modules\User\Models\User;

Broadcast::channel(
    'users.{id}',
    fn (User $user, int $id): bool => $user->getKey() === $id,
);
