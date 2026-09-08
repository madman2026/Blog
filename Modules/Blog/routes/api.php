<?php

use Illuminate\Support\Facades\Route;
use Modules\Blog\Http\Controllers\Api\V1\CategoryController;
use Modules\Blog\Http\Controllers\Api\V1\FeaturedImageController;
use Modules\Blog\Http\Controllers\Api\V1\ManagedPostController;
use Modules\Blog\Http\Controllers\Api\V1\PostController;
use Modules\Blog\Http\Controllers\Api\V1\ReviewPostController;
use Modules\Blog\Http\Controllers\Api\V1\SearchController;
use Modules\Blog\Http\Controllers\Api\V1\SubmitPostController;
use Modules\Blog\Http\Controllers\Api\V1\TagController;

Route::prefix('v1')->name('v1.')->group(function (): void {
    Route::get('{locale}/posts', [PostController::class, 'index'])->name('posts.index');
    Route::get('{locale}/posts/{postTranslation}', [PostController::class, 'show'])->name('posts.show');
    Route::get('{locale}/categories', [CategoryController::class, 'publicIndex'])->name('categories.index');
    Route::get('{locale}/tags', [TagController::class, 'publicIndex'])->name('tags.index');
    Route::get('{locale}/search', SearchController::class)->name('search');

    Route::middleware('auth:sanctum')->prefix('management')->name('management.')->group(function (): void {
        Route::apiResource('posts', ManagedPostController::class)
            ->parameters(['posts' => 'managedPost']);
        Route::post('posts/{managedPost}/submit', SubmitPostController::class)
            ->name('posts.submit');
        Route::patch('posts/{managedPost}/review', ReviewPostController::class)
            ->name('posts.review');
        Route::put('posts/{managedPost}/featured-image', FeaturedImageController::class)
            ->name('posts.featured-image');
        Route::apiResource('categories', CategoryController::class)->except(['create', 'edit']);
        Route::apiResource('tags', TagController::class)->except(['create', 'edit']);
    });
});
