<?php

namespace App\Exceptions\Auth;

use Exception;

abstract class AuthException extends Exception
{
    public function __construct(
        string $message = 'Authentication error.',
        protected int $statusCode = 400
    ) {
        parent::__construct($message, $statusCode);
    }

    public function statusCode(): int
    {
        return $this->getCode();
    }
}