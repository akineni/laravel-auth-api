<?php

use App\Http\Middleware\EnsureTenantIsActive;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then:function(){
            Route::prefix('api/v1')->group(function () {
                // Add more route groups as needed
            });
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([

            // Spatie Permission middlewares:
            'role'                => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'          => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission'  => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptionRenderers = require __DIR__.'/exceptions.php';
        $exceptionRenderers($exceptions);
    })->create();
