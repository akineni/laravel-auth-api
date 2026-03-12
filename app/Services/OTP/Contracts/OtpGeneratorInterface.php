<?php

namespace App\Services\OTP\Contracts;

interface OtpGeneratorInterface
{
    /**
     * Generate a new OTP code.
     */
    public function generate(): string;
}