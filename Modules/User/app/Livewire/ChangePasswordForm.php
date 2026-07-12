<?php

namespace Modules\User\Livewire;

use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Validate;
use Livewire\Form;

class ChangePasswordForm extends Form
{
    #[Validate('required|string|min:6|confirmed')]
    public string $password;

    public string $password_confirmation;

    public function store()
    {
        $this->validate();
        $user = auth('web')->user();
        $user->password = Hash::make($this->password);
        $user->save();
    }
}
