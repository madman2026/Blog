<?php

use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Livewire\Component;
use Masmerise\Toaster\Toastable;
use Modules\Auth\Actions\AuthenticateUser;
use Modules\Auth\Livewire\LoginForm;

new class extends Component
{
    use Toastable;

    public LoginForm $form;

    public function login(AuthenticateUser $authenticate): void
    {
        $this->form->authenticate($authenticate);
        $this->success(__('auth.successfully'));
        $this->redirectRoute('home', navigate: true);
    }

    public function render(): Illuminate\Contracts\View\View|Factory|View
    {
        return view('auth::livewire.pages.⚡login.login')->title(__('auth::login.title'));
    }
};
