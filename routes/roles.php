<?php

use App\Enums\PermissionEnum;
use App\Http\Controllers\v1\RoleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'jwt.session.activity'])
    ->prefix('roles')
    ->name('roles.')
    ->group(function () {
        Route::get('permissions', [RoleController::class, 'getPermissions'])
            ->name('permissions')
            ->middleware('permission:' . PermissionEnum::USER_MANAGEMENT_VIEW->value);

        Route::get('/', [RoleController::class, 'index'])
            ->name('index')
            ->middleware('permission:' . PermissionEnum::USER_MANAGEMENT_VIEW->value);

        Route::get('{role}', [RoleController::class, 'show'])
            ->name('show')
            ->middleware('permission:' . PermissionEnum::USER_MANAGEMENT_VIEW->value);

        Route::post('/', [RoleController::class, 'store'])
            ->name('store')
            ->middleware('permission:' . PermissionEnum::USER_MANAGEMENT_CREATE->value);

        Route::patch('{role}', [RoleController::class, 'update'])
            ->name('update')
            ->middleware('permission:' . PermissionEnum::USER_MANAGEMENT_EDIT->value);

        Route::delete('{role}', [RoleController::class, 'destroy'])
            ->name('destroy')
            ->middleware('permission:' . PermissionEnum::USER_MANAGEMENT_DELETE->value);
    });