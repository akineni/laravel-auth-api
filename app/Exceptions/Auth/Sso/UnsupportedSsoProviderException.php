<?php

namespace App\Exceptions\Auth\Sso;

class UnsupportedSsoProviderException extends SsoException
{
    public function __construct(string $provider, array $supportedProviders = [])
    {
        $providers = implode(', ', $supportedProviders ?: ['google']);

        parent::__construct(
            message: "Unsupported SSO provider '{$provider}'. Supported providers: {$providers}.",
            statusCode: 400
        );
    }
}