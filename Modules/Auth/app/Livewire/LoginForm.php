<?php

namespace Modules\Auth\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

class LoginForm extends Form
{
    #[Validate('required|email')]
    public string $email = '';

    #[Validate('required|string|min:6')]
    public string $password = '';

    #[Validate('boolean')]
    public bool $remember = false;

    public function authenticate(): bool
    {
        $credentials = $this->validate();
        unset($credentials['remember']);
        if (! Auth::attempt($credentials, $this->remember)) {
            $this->addError('email' , __('auth.failed'));
           return false;
        }
        return true;
        
        session()->regenerate();
    }
}