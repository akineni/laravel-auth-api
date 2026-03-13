<?php

namespace App\Exceptions\Auth;

class InactiveAccountException extends AuthException
{
    public function __construct(string $message = 'Account is inactive. Please contact support.')
    {
        parent::__construct($message, 403);
    }
}