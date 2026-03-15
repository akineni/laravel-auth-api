<?php

use App\Http\Controllers\v1\Auth\{AuthController, SsoController};
use App\Http\Controllers\v1\User\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::post('register', 'register')->name('auth.register');
        Route::post('login', 'login')->name('auth.login');
        Route::post('verify-otp', 'verifyOtp')->name('auth.verify-otp');
        Route::post('resend-otp', 'resendOtp')->name('auth.resend-otp');
        Route::post('forgot-password', 'forgotPassword')->name('auth.forgot-password');
        Route::post('reset-password', 'resetPassword')->name('auth.reset-password');

        Route::middleware('auth:api')->group(function () {
            Route::post('refresh-token', 'refreshToken')->name('auth.refresh-token');
            Route::post('logout', 'logout')->name('auth.logout');
        });
    });

    Route::post('activate-account', [UserController::class, 'activateUserAccount'])
            ->name('activate-account');

    Route::prefix('sso')
        ->controller(SsoController::class)
        ->group(function () {
            Route::get('{provider}/url', 'getAuthUrl')->name('auth.sso.url');
            Route::get('{provider}/callback', 'handleCallback')->name('auth.sso.callback');
            Route::post('exchange', 'exchangeCode')->name('auth.sso.exchange');
        });
});