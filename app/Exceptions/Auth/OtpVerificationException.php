<?php

namespace App\Exceptions\Auth;

class OtpVerificationException extends AuthException
{
    public function __construct(string $message = 'OTP verification failed.')
    {
        parent::__construct($message, 401);
    }
}