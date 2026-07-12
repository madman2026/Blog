<?php

use Illuminate\Support\Facades\Route;

Route::as('auth.')->prefix('auth')->group(function () {
    Route::livewire('login' , 'auth::pages.login')->middleware('guest')->name('login');    
    Route::livewire('register' , 'auth::pages.register')->middleware('guest')->name('register');    
    Route::livewire('logout' , 'auth::pages.logout')->middleware('auth')->name('logout');    
    Route::livewire('forget-password' , 'auth::pages.forget-password')->middleware('guest')->name('forget-password');    
});
