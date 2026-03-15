<?php

use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    /**
     * Health check
     *
     * Simple endpoint used to verify that the API service is running
     * and reachable. Often used by load balancers, monitoring systems,
     * or uptime checks.
     *
     * @group System
     */
    Route::get('health', function () {
        return ApiResponse::success();
    });

    require __DIR__ . '/auth.php';
    require __DIR__ . '/users.php';
    require __DIR__ . '/roles.php';
    require __DIR__ . '/settings.php';
});