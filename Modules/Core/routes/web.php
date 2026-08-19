<?php

use Illuminate\Support\Facades\Route;

Route::group([], function () {
    Route::as('help.')->prefix('help')->group(function () {
        Route::livewire('/', 'core::help.index')->name('index');
        Route::livewire('/{Help}', 'core::help.create')->name('create');
        Route::livewire('/{Help}', 'core::help.delete')->name('delete');
        Route::livewire('/{Help}', 'core::help.update')->name('update');
    });
});
