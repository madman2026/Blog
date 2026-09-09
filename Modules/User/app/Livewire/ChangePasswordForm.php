<?php

namespace Modules\User\Livewire;

use Illuminate\Validation\Rules\Password;
use Livewire\Form;
use Modules\User\Actions\UpdatePassword;

class ChangePasswordForm extends Form
{
    public string $currentPassword = '';

    public string $password = '';

    public string $passwordConfirmation = '';

    public function store(UpdatePassword $updatePassword): void
    {
        $validated = $this->validate([
            'currentPassword' => ['required', 'current_password:web'],
            'password' => ['required', 'confirmed:passwordConfirmation', Password::defaults()],
            'passwordConfirmation' => ['required', 'string'],
        ]);

        $updatePassword->handle(auth()->user(), $validated['password']);

        $this->reset();
    }
}
