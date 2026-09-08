<?php

use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;

Route::livewire('/', 'home')->name('home');
Route::post('/locale/{locale}', LocaleController::class)->name('locale.update');
Route::livewire('/dashboard', 'dashboard')->middleware('auth')->name('dashboard');
Route::livewire('/notifications', 'notifications')->middleware('auth')->name('notifications.index');
