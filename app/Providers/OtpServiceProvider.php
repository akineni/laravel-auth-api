<?php

namespace App\Providers;

use App\Enums\OtpMethodEnum;
use App\Repositories\Contracts\AuthChallengeRepositoryInterface;
use App\Repositories\Eloquent\AuthChallengeRepository;
use App\Services\Auth\TwoFactor\Drivers\AuthenticatorAppTwoFactorDriver;
use App\Services\Auth\TwoFactor\Drivers\DefaultTwoFactorDriver;
use App\Services\Auth\TwoFactor\TwoFactorDriverManager;
use App\Services\OTP\Channels\EmailOtpChannel;
use App\Services\OTP\Channels\SmsOtpChannel;
use App\Services\OTP\Contracts\OtpGeneratorInterface;
use App\Services\OTP\Generators\NumericOtpGenerator;
use App\Services\OTP\SendOtpService;
use Illuminate\Support\ServiceProvider;
use PragmaRX\Google2FA\Google2FA;

class OtpServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            AuthChallengeRepositoryInterface::class,
            AuthChallengeRepository::class
        );

        $this->app->bind(OtpGeneratorInterface::class, function () {
            return new NumericOtpGenerator(6);
        });

        $this->app->singleton(SendOtpService::class, function ($app) {
            return new SendOtpService(
                generator: $app->make(OtpGeneratorInterface::class),
                authChallengeRepository: $app->make(AuthChallengeRepositoryInterface::class),
                methodHandlers: [
                    OtpMethodEnum::OTP_EMAIL->value => $app->make(EmailOtpChannel::class),
                    OtpMethodEnum::OTP_SMS->value => $app->make(SmsOtpChannel::class),
                ],
            );
        });

        $this->app->singleton(Google2FA::class, fn () => new Google2FA());

        $this->app->singleton(TwoFactorDriverManager::class, function ($app) {
            return new TwoFactorDriverManager([
                $app->make(DefaultTwoFactorDriver::class),
                $app->make(AuthenticatorAppTwoFactorDriver::class),
            ]);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}