<?php

namespace Modules\Auth\Livewire;

use Illuminate\Validation\Rules\Password;
use Livewire\Form;
use Modules\Auth\Actions\RegisterUser;
use Modules\Auth\Data\RegisterUserData;
use Modules\User\Models\User;

class RegisterForm extends Form
{
    public string $email = '';

    public string $username = '';

    public string $phone = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function store(RegisterUser $register): User
    {
        $validated = $this->validate();

        return $register->handle(new RegisterUserData(
            username: $validated['username'],
            email: $validated['email'],
            phone: $validated['phone'],
            password: $validated['password'],
            preferredLocale: app()->getLocale(),
        ));
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
