<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Masmerise\Toaster\Toastable;
use Modules\Auth\Livewire\LoginForm;

new #[Layout("auth::components.layouts.master")] #[Title("Login")] class extends Component
{
    use Toastable;
    public LoginForm $form;

    public function login()
    {
        $this->form->authenticate();

        $this->success('login successfuly');

        return 'safdsf';
    }
};
