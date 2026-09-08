<?php

use Illuminate\Support\Facades\Route;

Route::as('user.')->prefix('user')->group(function () {
    Route::livewire('change-password', 'user::pages.change-password')->middleware('auth')->name('change-password');
    Route::livewire('settings', 'user::pages.settings')->middleware('auth')->name('settings');
    Route::livewire('{user}', 'user::pages.profile-user')->name('profile');
});
