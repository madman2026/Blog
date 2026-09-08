<?php

namespace Modules\User\Livewire;

use Illuminate\Validation\Rules\Password;
use Livewire\Form;

class ChangePasswordForm extends Form
{
    public string $currentPassword = '';

    public string $password = '';

    public string $passwordConfirmation = '';

    public function store(): void
    {
        $validated = $this->validate([
            'currentPassword' => ['required', 'current_password:web'],
            'password' => ['required', 'confirmed:passwordConfirmation', Password::defaults()],
            'passwordConfirmation' => ['required', 'string'],
        ]);

        $user = auth()->user();
        $user->update(['password' => $validated['password']]);
        $user->tokens()->delete();

        $this->reset();
    }
}
