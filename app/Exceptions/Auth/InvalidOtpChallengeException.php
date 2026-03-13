<?php

namespace App\Exceptions\Auth;

class InvalidOtpChallengeException extends AuthException
{
    public function __construct(string $message = 'Invalid or expired OTP challenge.')
    {
        parent::__construct($message, 400);
    }
}