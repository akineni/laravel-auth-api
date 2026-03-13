<?php

namespace App\Exceptions\Auth;

class AccountLockedException extends AuthException
{
    public function __construct(string $message = 'Account locked. Try again later.')
    {
        parent::__construct($message, 423);
    }
}