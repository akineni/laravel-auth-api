<?php

namespace App\Exceptions\Auth\Sso;

class SsoInvalidCodeException extends SsoException
{
    public function __construct(string $message = 'Invalid or expired SSO code.')
    {
        parent::__construct(
            message: $message,
            statusCode: 401
        );
    }
}