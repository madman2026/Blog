<?php

namespace Modules\User\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\User\Enums\UserStatus;
use Modules\User\Models\User;

final class ManagedUserStatusChanged implements ShouldDispatchAfterCommit
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public User $user, public UserStatus $status) {}
}
