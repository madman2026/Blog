<?php

use Illuminate\Support\Facades\Route;
use Modules\User\Http\Controllers\Api\V1\AuthorApplicationController;
use Modules\User\Http\Controllers\Api\V1\AuthorApplicationReviewController;
use Modules\User\Http\Controllers\Api\V1\AvatarController;
use Modules\User\Http\Controllers\Api\V1\ManagedUserController;
use Modules\User\Http\Controllers\Api\V1\NotificationController;
use Modules\User\Http\Controllers\Api\V1\ProfileController;

Route::middleware('auth:sanctum')->prefix('v1')->name('v1.')->group(function (): void {
    Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/avatar', AvatarController::class)->name('profile.avatar');
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('notifications/read-all', [NotificationController::class, 'markAllRead'])
        ->name('notifications.read-all');
    Route::patch('notifications/{notification}/read', [NotificationController::class, 'markRead'])
        ->name('notifications.read');

    Route::get('author-applications/current', [AuthorApplicationController::class, 'current'])
        ->name('author-applications.current');
    Route::post('author-applications', [AuthorApplicationController::class, 'store'])
        ->name('author-applications.store');
    Route::get('management/author-applications', [AuthorApplicationController::class, 'index'])
        ->name('management.author-applications.index');
    Route::patch(
        'management/author-applications/{authorApplication}/review',
        AuthorApplicationReviewController::class,
    )->name('management.author-applications.review');
    Route::get('management/users', [ManagedUserController::class, 'index'])
        ->name('management.users.index');
    Route::get('management/users/{managedUser}', [ManagedUserController::class, 'show'])
        ->name('management.users.show');
    Route::patch('management/users/{managedUser}/status', [ManagedUserController::class, 'updateStatus'])
        ->name('management.users.status');
    Route::patch('management/users/{managedUser}/role', [ManagedUserController::class, 'updateRole'])
        ->name('management.users.role');
});
