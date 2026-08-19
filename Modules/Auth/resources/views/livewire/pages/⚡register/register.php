<?php

use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use Livewire\Component;

new class extends Component
{
    public function render(): Illuminate\Contracts\View\View|Factory|View
    {
        return view('auth::livewire.pages.⚡register.register')->title(__('auth::register_title'));
    }
};
