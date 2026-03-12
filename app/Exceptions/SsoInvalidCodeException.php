<?php

namespace App\Exceptions;

use Exception;

class SsoInvalidCodeException extends Exception
{
    public function __construct($message = "Invalid or expired SSO code.", $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
