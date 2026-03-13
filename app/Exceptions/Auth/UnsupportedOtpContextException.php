<?php

namespace App\Exceptions\Auth;

class UnsupportedOtpContextException extends AuthException
{
    public function __construct(string $message = 'Unsupported OTP context.')
    {
        parent::__construct($message, 400);
    }
}