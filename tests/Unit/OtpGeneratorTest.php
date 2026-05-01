<?php

namespace Tests\Unit;

use App\Services\OTP\Generators\NumericOtpGenerator;
use Tests\TestCase;

class OtpGeneratorTest extends TestCase
{
    public function test_generates_otp_with_correct_length(): void
    {
        $generator = new NumericOtpGenerator(6);
        $otp = $generator->generate();

        $this->assertSame(6, strlen($otp));
    }

    public function test_generated_otp_is_numeric(): void
    {
        $generator = new NumericOtpGenerator(6);
        $otp = $generator->generate();

        $this->assertMatchesRegularExpression('/^\d+$/', $otp);
    }

    public function test_generates_otp_with_custom_length(): void
    {
        $generator = new NumericOtpGenerator(8);
        $otp = $generator->generate();

        $this->assertSame(8, strlen($otp));
    }

    public function test_does_not_start_with_zero(): void
    {
        $generator = new NumericOtpGenerator(6);

        for ($i = 0; $i < 20; $i++) {
            $otp = $generator->generate();
            $this->assertNotSame('0', $otp[0]);
        }
    }
}