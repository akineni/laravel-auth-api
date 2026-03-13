<?php

namespace App\Exceptions\Auth;

class UnsupportedOtpChannelException extends AuthException
{
    public function __construct(string $message = 'Unsupported OTP channel.')
    {
        parent::__construct($message, 400);
    }
}