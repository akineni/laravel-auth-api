<?php

use App\Http\Controllers\v1\User\TwoFactorAuthController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'jwt.session.activity'])
    ->prefix('user')
    ->group(function () {
        Route::prefix('two-fa')->group(function () {
            Route::get('authenticator/qr-code', [TwoFactorAuthController::class, 'renderAuthenticatorQrCode'])
                ->name('user.two-fa.authenticator.qr-code');
            Route::post('authenticator/setup', [TwoFactorAuthController::class, 'setupAuthenticator']);
            Route::post('authenticator/confirm', [TwoFactorAuthController::class, 'confirmAuthenticator'])
                ->middleware('throttle:10,1');
            Route::post('enable', [TwoFactorAuthController::class, 'enable']);
            Route::delete('disable', [TwoFactorAuthController::class, 'disable']);
            Route::post('method', [TwoFactorAuthController::class, 'switchMethod']);
            Route::post('recovery-codes/regenerate', [TwoFactorAuthController::class, 'regenerateRecoveryCodes']);
        });
    });