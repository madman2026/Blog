<?php

namespace Modules\Auth\Livewire;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Livewire\Form;
use Modules\User\Actions\IssuePhoneVerificationChallenge;
use Modules\User\Enums\UserRole;
use Modules\User\Models\User;

class RegisterForm extends Form
{
    public string $email = '';

    public string $username = '';

    public string $phone = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function store(): User
    {
        $validated = $this->validate();

        $user = DB::transaction(function () use ($validated): User {
            $user = User::query()->create([
                'email' => mb_strtolower($validated['email']),
                'username' => $validated['username'],
                'phone' => $validated['phone'],
                'password' => $validated['password'],
                'preferred_locale' => app()->getLocale(),
            ]);
            $user->assignRole(UserRole::User->value);

            return $user;
        });

        $user->sendEmailVerificationNotification();
        app(IssuePhoneVerificationChallenge::class)->handle($user);

        return $user;
    }

    /** @return array<string, array<int, mixed>> */
    protected function rules(): array
    {
        return [
            'email' => ['required', 'email:rfc', 'max:255', 'unique:users,email'],
            'username' => ['required', 'string', 'min:3', 'max:32', 'alpha_dash:ascii', 'unique:users,username'],
            'phone' => ['required', 'string', 'regex:/^\+[1-9]\d{7,14}$/', 'unique:users,phone'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }
}
