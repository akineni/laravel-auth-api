<?php

namespace App\Exceptions\Auth;

class ExpiredOtpException extends AuthException
{
    public function __construct(string $message = 'OTP has expired. Please request a new one.')
    {
        parent::__construct($message, 400);
    }
}