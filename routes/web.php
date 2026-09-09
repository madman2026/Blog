<?php

use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;

Route::livewire('/', 'home')->name('home');
Route::post('/locale/{locale}', LocaleController::class)->name('locale.update');
Route::livewire('/dashboard', 'dashboard')->middleware('auth')->name('dashboard');
Route::livewire('/notifications', 'notifications')->middleware('auth')->name('notifications.index');

Route::middleware(['auth', 'permission:posts.view-any'])
    ->prefix('writer')
    ->name('writer.')
    ->group(function (): void {
        Route::livewire('/', 'writer.dashboard')->name('dashboard');
    });

Route::middleware('auth')
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::livewire('/', 'admin.dashboard')->middleware('permission:users.view-any')->name('dashboard');
        Route::livewire('/comments', 'admin.comments')->middleware('permission:comments.view-any')->name('comments');
        Route::livewire('/author-applications', 'admin.applications')->middleware('permission:author-applications.review')->name('applications');
        Route::livewire('/taxonomies', 'admin.taxonomies')->middleware('permission:taxonomies.manage')->name('taxonomies');
        Route::livewire('/users', 'admin.users')->middleware('permission:users.view-any')->name('users');
    });
