<?php

namespace App\Exceptions\Auth;

class InvalidResetTokenException extends AuthException
{
    public function __construct(string $message = 'Invalid or expired reset token.')
    {
        parent::__construct($message, 400);
    }
}