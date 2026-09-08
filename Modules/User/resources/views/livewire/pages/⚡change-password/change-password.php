<?php

use Livewire\Component;
use Masmerise\Toaster\Toastable;
use Modules\User\Livewire\ChangePasswordForm;

new class extends Component
{
    use Toastable;

    public ChangePasswordForm $form;

    public function save(): void
    {
        $this->form->store();
        $this->success(__('Password updated.'));
    }
};
