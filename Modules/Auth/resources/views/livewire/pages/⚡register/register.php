<?php

use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;
use Modules\Auth\Livewire\RegisterForm;

new class extends Component
{
    public RegisterForm $form;

    public function register(): void
    {
        $user = $this->form->store();
        Auth::login($user);
        session()->regenerate();

        $this->redirectRoute('dashboard', navigate: true);
    }

    public function render(): Illuminate\Contracts\View\View|Factory|View
    {
        return view('auth::livewire.pages.⚡register.register')->title(__('auth::register_title'));
    }
};
