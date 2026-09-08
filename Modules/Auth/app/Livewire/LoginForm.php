<?php

namespace Modules\Auth\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Validate;
use Livewire\Form;
use Modules\User\Enums\UserStatus;
use Modules\User\Models\User;

class LoginForm extends Form
{
    #[Validate('required|string|max:255')]
    public string $identifier = '';

    #[Validate('required|string|min:6')]
    public string $password = '';

    #[Validate('boolean')]
    public bool $remember = false;

    public function authenticate(): bool
    {
        $validated = $this->validate();
        $column = filter_var($validated['identifier'], FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        $user = User::query()->where($column, $validated['identifier'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            $this->addError('identifier', __('auth.failed'));

            return false;
        }

        if ($user->status === UserStatus::Suspended) {
            $this->addError('identifier', __('This account is suspended.'));

            return false;
        }

        Auth::login($user, $validated['remember']);
        session()->regenerate();

        return true;
    }
}
