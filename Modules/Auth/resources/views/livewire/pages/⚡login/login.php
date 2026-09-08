<?php

use Livewire\Attributes\Title;
use Livewire\Component;
use Masmerise\Toaster\Toastable;
use Modules\Auth\Livewire\LoginForm;

new #[Title('Login')] class extends Component
{
    use Toastable;

    public LoginForm $form;

    public function login(): void
    {
        if ($this->form->authenticate())
        {
            $this->success(__('auth.successfully'));
            $this->redirectRoute('home', navigate: true);
        }else{
            $this->error(__('auth.failed'));
        }
    }
};
