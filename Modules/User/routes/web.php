<?php

use Illuminate\Support\Facades\Route;

Route::as('user.')->prefix('user')->group(function () {
    Route::livewire('change-password', 'user::pages.change-password')->name('change-password');
    Route::livewire('{User}', 'user::pages.profile-user')->name('profile');
});