<?php

use App\Http\Controllers\v1\User\TwoFactorAuthController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')
    ->prefix('user')
    ->group(function () {
        Route::prefix('two-fa')->group(function () {
            Route::get('authenticator/qr-code', [TwoFactorAuthController::class, 'renderAuthenticatorQrCode'])
                ->name('user.two-fa.authenticator.qr-code');
            Route::post('authenticator/setup', [TwoFactorAuthController::class, 'setupAuthenticator']);
            Route::post('authenticator/confirm', [TwoFactorAuthController::class, 'confirmAuthenticator'])
                ->middleware('throttle:10,1');
            Route::delete('authenticator/disable', [TwoFactorAuthController::class, 'disableAuthenticator']);
            Route::post('recovery-codes/regenerate', [TwoFactorAuthController::class, 'regenerateRecoveryCodes']);
        });
    });