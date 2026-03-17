<?php

namespace App\Providers;

use App\Services\SMS\Contracts\SmsProviderInterface;
use App\Services\SMS\Providers\TermiiSmsProvider;
use App\Services\SMS\Providers\TwilioSmsProvider;
use App\Services\SMS\SmsService;
use Illuminate\Support\ServiceProvider;

class SmsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // $this->app->bind(SmsProviderInterface::class, TwilioSmsProvider::class);
        $this->app->bind(SmsProviderInterface::class, TermiiSmsProvider::class);

        $this->app->singleton(SmsService::class, function ($app) {
            return new SmsService(
                $app->make(SmsProviderInterface::class)
            );
        });
    }

    public function boot(): void
    {
        //
    }
}