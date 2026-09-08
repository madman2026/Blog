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

        Route::livewire('/posts', 'blog::post.list')
            ->name('posts.index');

        Route::livewire('/posts/{postTranslation}', 'blog::post.show')
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

                Route::livewire('/posts', 'blog::post.index')
                    ->middleware('permission:posts.view-any')
                    ->name('posts.index');

                Route::livewire('/posts/create', 'blog::post.post')
                    ->middleware('permission:posts.create')
                    ->name('posts.create');

                Route::livewire('/posts/{post}/edit', 'blog::post.post')
                    ->name('posts.edit');

            });

    });
