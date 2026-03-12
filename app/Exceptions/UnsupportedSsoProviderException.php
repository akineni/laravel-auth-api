<?php

namespace App\Exceptions;

use Exception;

class UnsupportedSsoProviderException extends Exception
{
    public function __construct(string $provider, array $supportedProviders = [])
    {
        $providers = implode(', ', $supportedProviders ?: ['google']);

        parent::__construct(
            "Unsupported SSO provider: '{$provider}'. Supported providers are: {$providers}."
        );
    }
}
