<?php

namespace Modules\Auth\Actions;

use Modules\Auth\Data\LoginCredentials;
use Modules\Auth\Services\AuthService;
use Modules\User\Models\User;

final readonly class AuthenticateUser
{
    public function __construct(private AuthService $auth) {}

    public function handle(LoginCredentials $credentials): User
    {
        return $this->auth->authenticate($credentials);
    }
}
