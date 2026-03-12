<?php

namespace App\Services\Auth\SSO;

use App\Exceptions\UnsupportedSsoProviderException;
use App\Services\Auth\SSO\Contracts\SsoProviderInterface;

class SsoProviderFactory
{
    public static function make(string $provider): SsoProviderInterface
    {
        $provider = strtolower($provider);

        $providers = config('sso.providers', []);

        if (!array_key_exists($provider, $providers)) {
            throw new UnsupportedSsoProviderException(
                $provider,
                array_keys($providers)
            );
        }

        return app($providers[$provider]);
    }
}