<?php

namespace Modules\Auth\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Form;
use Modules\Auth\Actions\AuthenticateUser;
use Modules\Auth\Data\LoginCredentials;

class LoginForm extends Form
{
    #[Validate('required|string|max:255')]
    public string $identifier = '';

    #[Validate('required|string|min:6')]
    public string $password = '';

    #[Validate('boolean')]
    public bool $remember = false;

    public function authenticate(AuthenticateUser $authenticate): void
    {
        $validated = $this->validate();
        $user = $authenticate->handle(new LoginCredentials(
            identifier: $validated['identifier'],
            password: $validated['password'],
        ));

        Auth::login($user, $validated['remember']);
        session()->regenerate();

    }
}
