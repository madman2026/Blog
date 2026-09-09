<?php

use Illuminate\Validation\Rules\Password as PasswordRule;
use Livewire\Component;
use Modules\Auth\Actions\CompletePasswordReset;

new class extends Component
{
    public string $token = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->email = (string) request()->query('email');
    }

    public function resetPassword(CompletePasswordReset $completePasswordReset): void
    {
        $validated = $this->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        $completePasswordReset->handle($validated);

        $this->redirectRoute('auth.login', navigate: true);
    }
};
