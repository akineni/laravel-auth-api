<?php

use App\Http\Controllers\v1\User\NotificationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'jwt.session.activity'])
    ->prefix('notifications')
    ->controller(NotificationController::class)
    ->group(function () {
        Route::get('/', 'index')->name('notifications.index');

        Route::get('unread-count', 'unreadCount')
            ->name('notifications.unread-count');

        Route::get('{notification}', 'show')
            ->name('notifications.show');

        Route::patch('{notification}/read', 'markAsRead')
            ->name('notifications.read');

        Route::patch('read-all', 'markAllAsRead')
            ->name('notifications.read-all');

        Route::delete('{notification}', 'destroy')
            ->name('notifications.destroy');
    });