<?php

use Illuminate\Support\Facades\Route;
use Modules\Interaction\Http\Controllers\Api\V1\BookmarkedPostController;
use Modules\Interaction\Http\Controllers\Api\V1\CommentController;
use Modules\Interaction\Http\Controllers\Api\V1\CommentModerationController;
use Modules\Interaction\Http\Controllers\Api\V1\PendingCommentController;
use Modules\Interaction\Http\Controllers\Api\V1\ToggleBookmarkController;
use Modules\Interaction\Http\Controllers\Api\V1\ToggleLikeController;

Route::prefix('v1')->name('v1.')->group(function (): void {
    Route::get('{locale}/posts/{postTranslation}/comments', [CommentController::class, 'index'])
        ->name('posts.comments.index');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('{locale}/bookmarks', BookmarkedPostController::class)
            ->name('bookmarks.index');
        Route::post('{locale}/posts/{postTranslation}/comments', [CommentController::class, 'store'])
            ->middleware('throttle:20,1')
            ->name('posts.comments.store');
        Route::put('{locale}/posts/{postTranslation}/like', ToggleLikeController::class)
            ->name('posts.like');
        Route::put('{locale}/posts/{postTranslation}/bookmark', ToggleBookmarkController::class)
            ->name('posts.bookmark');
        Route::get('management/comments/pending', PendingCommentController::class)
            ->name('management.comments.pending');
        Route::patch('management/comments/{comment}/moderate', CommentModerationController::class)
            ->name('management.comments.moderate');
    });
});
