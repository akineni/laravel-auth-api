<?php

namespace App\Providers;

use App\Repositories\Contracts\{
    AuthSessionRepositoryInterface,
    NotificationRepositoryInterface,
    PasswordResetTokenRepositoryInterface,
    RoleRepositoryInterface,
    UserRepositoryInterface,
    UserRoleRepositoryInterface
};

use App\Repositories\Eloquent\{
    AuthSessionRepository,
    NotificationRepository,
    PasswordResetTokenRepository,
    RoleRepository,
    UserRepository,
    UserRoleRepository
};
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            UserRepositoryInterface::class,
            UserRepository::class
        );

        $this->app->bind(
            PasswordResetTokenRepositoryInterface::class,
            PasswordResetTokenRepository::class
        );

        $this->app->bind(
            RoleRepositoryInterface::class,
            RoleRepository::class
        );

        $this->app->bind(
            UserRoleRepositoryInterface::class,
            UserRoleRepository::class
        );

        $this->app->bind(
            AuthSessionRepositoryInterface::class,
            AuthSessionRepository::class
        );

        $this->app->bind(
            NotificationRepositoryInterface::class,
            NotificationRepository::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
