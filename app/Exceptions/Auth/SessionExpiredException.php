<?php

namespace App\Exceptions\Auth;

class SessionExpiredException extends AuthException
{
    public function __construct(
        string $message = 'Your session has expired due to inactivity. Please log in again.'
    ) {
        parent::__construct($message, 401);
    }
}