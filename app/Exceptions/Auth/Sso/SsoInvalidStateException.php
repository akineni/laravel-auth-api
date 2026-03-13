<?php

namespace App\Exceptions\Auth\Sso;

class SsoInvalidStateException extends SsoException
{
    public function __construct(string $message = 'Invalid or expired state.')
    {
        parent::__construct($message, 400);
    }
}