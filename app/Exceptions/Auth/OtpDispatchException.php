<?php

namespace App\Exceptions\Auth;

class OtpDispatchException extends AuthException
{
    public function __construct(string $message = 'Unable to send OTP at the moment.')
    {
        parent::__construct($message, 500);
    }
}