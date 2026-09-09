<?php

namespace Modules\Auth\Actions;

use Modules\Auth\Data\RegisterUserData;
use Modules\Auth\Events\UserRegistered;
use Modules\User\Interfaces\Repositories\UserRepository;
use Modules\User\Models\User;

final readonly class RegisterUser
{
    public function __construct(
        private UserRepository $users,
    ) {}

    public function handle(RegisterUserData $data): User
    {
        $user = $this->users->register([
            'username' => $data->username,
            'email' => mb_strtolower($data->email),
            'phone' => $data->phone,
            'password' => $data->password,
            'preferred_locale' => $data->preferredLocale,
        ]);

        UserRegistered::dispatch($user);

        return $user;
    }
}
