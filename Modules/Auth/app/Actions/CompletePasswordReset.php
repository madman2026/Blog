<?php

namespace Modules\Auth\Actions;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Modules\User\Interfaces\Repositories\UserRepository;
use Modules\User\Models\User;

final readonly class CompletePasswordReset
{
    public function __construct(private UserRepository $users) {}

    /** @param array{email: string, password: string, password_confirmation: string, token: string} $credentials */
    public function handle(array $credentials): void
    {
        $status = Password::reset(
            $credentials,
            function (User $user, string $password): void {
                $this->users->resetPassword($user, $password);
                event(new PasswordReset($user));
            },
        );

        if ($status !== Password::PasswordReset) {
            throw ValidationException::withMessages(['email' => __($status)]);
        }
    }
}
