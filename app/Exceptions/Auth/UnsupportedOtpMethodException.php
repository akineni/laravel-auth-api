<?php

namespace App\Exceptions\Auth;

class UnsupportedOtpMethodException extends AuthException
{
    public function __construct(string $message = 'Unsupported OTP method.')
    {
        parent::__construct($message, 400);
    }
}