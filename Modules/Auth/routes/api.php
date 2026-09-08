<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\Api\V1\EmailVerificationController;
use Modules\Auth\Http\Controllers\Api\V1\LoginController;
use Modules\Auth\Http\Controllers\Api\V1\LogoutController;
use Modules\Auth\Http\Controllers\Api\V1\PasswordResetController;
use Modules\Auth\Http\Controllers\Api\V1\PhoneVerificationController;
use Modules\Auth\Http\Controllers\Api\V1\RegisterController;

Route::get('v1/auth/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::prefix('v1/auth')->name('v1.auth.')->group(function (): void {
    Route::post('register', RegisterController::class)
        ->middleware('throttle:6,1')
        ->name('register');
    Route::post('login', LoginController::class)
        ->middleware('throttle:6,1')
        ->name('login');
    Route::post('logout', LogoutController::class)
        ->middleware('auth:sanctum')
        ->name('logout');
    Route::post('password/forgot', [PasswordResetController::class, 'forgot'])
        ->middleware('throttle:6,1')
        ->name('password.forgot');
    Route::post('password/reset', [PasswordResetController::class, 'reset'])
        ->middleware('throttle:6,1')
        ->name('password.reset');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('email/verification-notification', [EmailVerificationController::class, 'send'])
            ->middleware('throttle:6,1')
            ->name('email.send');
        Route::post('phone/verification-code', [PhoneVerificationController::class, 'send'])
            ->middleware('throttle:3,10')
            ->name('phone.send');
        Route::post('phone/verify', [PhoneVerificationController::class, 'verify'])
            ->middleware('throttle:6,1')
            ->name('phone.verify');
    });
});
