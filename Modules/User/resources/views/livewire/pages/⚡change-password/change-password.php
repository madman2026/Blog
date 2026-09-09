<?php

use Livewire\Component;
use Masmerise\Toaster\Toastable;
use Modules\User\Actions\UpdatePassword;
use Modules\User\Livewire\ChangePasswordForm;

new class extends Component
{
    use Toastable;

    public ChangePasswordForm $form;

    public function save(UpdatePassword $updatePassword): void
    {
        $this->form->store($updatePassword);
        $this->success(__('Password updated.'));
    }
};
