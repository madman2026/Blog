<?php

namespace Modules\User\Actions;

use Modules\User\Interfaces\Repositories\UserRepository;
use Modules\User\Models\User;

final readonly class UpdatePassword
{
    public function __construct(private UserRepository $users) {}

    public function handle(User $user, string $password): void
    {
        $this->users->updatePassword($user, $password);
    }
}
