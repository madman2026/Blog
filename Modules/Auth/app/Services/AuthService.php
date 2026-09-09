<?php

namespace Modules\Auth\Services;

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Modules\Auth\Data\LoginCredentials;
use Modules\User\Enums\UserStatus;
use Modules\User\Interfaces\Repositories\UserRepository;
use Modules\User\Models\User;

final readonly class AuthService
{
    public function __construct(private UserRepository $users) {}

    public function authenticate(LoginCredentials $credentials): User
    {
        $identifier = mb_strtolower($credentials->identifier);
        $field = str_starts_with($identifier, '+') ? 'phone' : 'email';
        $user = $this->users->findForAuthentication($field, $identifier);

        if (! $user || ! Hash::check($credentials->password, $user->password)) {
            throw ValidationException::withMessages([
                'identifier' => __('auth.failed'),
            ]);
        }

        if ($user->status === UserStatus::Suspended) {
            throw ValidationException::withMessages([
                'identifier' => __('This account is suspended.'),
            ]);
        }

        return $user;
    }
}
