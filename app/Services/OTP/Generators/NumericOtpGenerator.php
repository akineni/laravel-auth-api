<?php

namespace App\Services\OTP\Generators;

use App\Services\OTP\Contracts\OtpGeneratorInterface;

class NumericOtpGenerator implements OtpGeneratorInterface
{
    public function __construct(
        protected int $length = 6
    ) {}

    public function generate(): string
    {
        $min = 10 ** ($this->length - 1);
        $max = (10 ** $this->length) - 1;

        return (string) random_int($min, $max);
    }
}