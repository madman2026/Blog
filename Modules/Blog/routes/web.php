<?php

use Illuminate\Support\Facades\Route;

Route::prefix('blog')
    ->name('blog.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Public
        |--------------------------------------------------------------------------
        */

        Route::livewire('/posts', 'blog::posts.list')
            ->name('posts.index');

        Route::livewire('/posts/{post}', 'blog::posts.show')
            ->name('posts.show');

        /*
        |--------------------------------------------------------------------------
        | Management
        |--------------------------------------------------------------------------
        */

        Route::middleware('auth')
            ->prefix('manage')
            ->name('manage.')
            ->group(function () {

                Route::livewire('/posts', 'blog::posts.index')
                    ->middleware('permission:posts.view')
                    ->name('posts.index');

                Route::livewire('/posts/create', 'blog::posts.post')
                    ->middleware('permission:posts.create')
                    ->name('posts.create');

                Route::livewire('/posts/{post}/edit', 'blog::posts.post')
                    ->middleware('permission:posts.update')
                    ->name('posts.edit');

            });

    });
