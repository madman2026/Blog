<?php

use Illuminate\Support\Facades\Password;
use Livewire\Component;

new class extends Component
{
    public string $email = '';

    public bool $sent = false;

    public function sendResetLink(): void
    {
        $validated = $this->validate([
            'email' => ['required', 'email:rfc', 'max:255'],
        ]);

        Password::sendResetLink(['email' => $validated['email']]);
        $this->sent = true;
    }
};
