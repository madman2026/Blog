<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Modules\User\Livewire\ChangePasswordForm;

new #[Layout('user::components.layouts.master')] class extends Component
{
    public ChangePasswordForm $form;
    public function save()
    {
        $this->form->store();

        return $this->redirect(route('home' , true));
    }
};