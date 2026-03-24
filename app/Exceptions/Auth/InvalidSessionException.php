<?php

namespace App\Exceptions\Auth;

class InvalidSessionException extends AuthException
{
    public function __construct(
        string $message = 'Invalid session. Please log in again.'
    ) {
        parent::__construct($message, 401);
    }
}