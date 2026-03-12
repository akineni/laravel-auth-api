<?php

use App\Enums\PermissionEnum;
use App\Http\Controllers\v1\{UserController, UserRoleController};
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')
    ->prefix('users')
    ->group(function () {

        Route::prefix('me')
            ->name('users.me.')
            ->group(function () {

                Route::get('/', [UserController::class, 'currentUser'])
                    ->name('');

                Route::patch('/', [UserController::class, 'updateMe'])
                    ->name('update');

                Route::patch('password', [UserController::class, 'changeMyPassword'])
                    ->name('change-password');

            });

        Route::post('/', [UserController::class, 'store'])
            ->name('users.store')
            ->middleware('permission:' . PermissionEnum::USER_MANAGEMENT_CREATE->value);

        Route::get('/', [UserController::class, 'index'])
            ->name('users.index')
            ->middleware('permission:' . PermissionEnum::USER_MANAGEMENT_VIEW->value);

        Route::scopeBindings()->prefix('{user}')->group(function () {
            Route::get('/', [UserController::class, 'show'])
                ->name('users.show')
                ->middleware('permission:' . PermissionEnum::USER_MANAGEMENT_VIEW->value);

            Route::patch('/', [UserController::class, 'update'])
                ->name('users.update')
                ->middleware('permission:' . PermissionEnum::USER_MANAGEMENT_EDIT->value);

            Route::patch('activate', [UserController::class, 'activateUser'])
                ->name('users.activate')
                ->middleware('permission:' . PermissionEnum::USER_MANAGEMENT_EDIT->value);

            Route::patch('deactivate', [UserController::class, 'deactivateUser'])
                ->name('users.deactivate')
                ->middleware('permission:' . PermissionEnum::USER_MANAGEMENT_EDIT->value);

            Route::delete('/', [UserController::class, 'destroy'])
                ->name('users.destroy')
                ->middleware('permission:' . PermissionEnum::USER_MANAGEMENT_DELETE->value);

            Route::prefix('roles')->group(function () {
                Route::post('assign', [UserRoleController::class, 'assignRole'])
                    ->name('users.roles.assign')
                    ->middleware('permission:' . PermissionEnum::USER_MANAGEMENT_EDIT->value);

                Route::post('revoke', [UserRoleController::class, 'revokeRole'])
                    ->name('users.roles.revoke')
                    ->middleware('permission:' . PermissionEnum::USER_MANAGEMENT_EDIT->value);
            });
        });
    });