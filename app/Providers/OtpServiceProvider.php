<?php

namespace App\Providers;

use App\Repositories\Contracts\OneTimePasswordRepositoryInterface;
use App\Repositories\Eloquent\OneTimePasswordRepository;
use App\Services\OTP\Channels\EmailOtpChannel;
use App\Services\OTP\Channels\SmsOtpChannel;
use App\Services\OTP\Contracts\OtpGeneratorInterface;
use App\Services\OTP\Contracts\OtpRepositoryInterface;
use App\Services\OTP\Generators\NumericOtpGenerator;
use App\Services\OTP\Repositories\UserOtpRepository;
use App\Services\OTP\SendOtpService;
use Illuminate\Support\ServiceProvider;

class OtpServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            OneTimePasswordRepositoryInterface::class,
            OneTimePasswordRepository::class
        );

        $this->app->bind(OtpGeneratorInterface::class, function () {
            return new NumericOtpGenerator(6);
        });

        $this->app->singleton(SendOtpService::class, function ($app) {
            return new SendOtpService(
                generator: $app->make(OtpGeneratorInterface::class),
                oneTimePasswordRepository: $app->make(OneTimePasswordRepositoryInterface::class),
                channels: [
                    'email' => $app->make(EmailOtpChannel::class),
                    'sms' => $app->make(SmsOtpChannel::class),
                ],
            );
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
