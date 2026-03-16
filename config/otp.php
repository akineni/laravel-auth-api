<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OTP Length
    |--------------------------------------------------------------------------
    |
    | Number of digits/characters the OTP generator should produce.
    |
    */

    'length' => env('OTP_LENGTH', 6),

    /*
    |--------------------------------------------------------------------------
    | OTP Expiration (minutes)
    |--------------------------------------------------------------------------
    |
    | How long an OTP remains valid before expiring.
    |
    */

    'expiry_minutes' => env('OTP_EXPIRY_MINUTES', 5),

    /*
    |--------------------------------------------------------------------------
    | OTP Attempts
    |--------------------------------------------------------------------------
    |
    | Maximum number of verification attempts allowed per challenge.
    |
    */

    'max_attempts' => env('OTP_MAX_ATTEMPTS', 5),

    /*
    |--------------------------------------------------------------------------
    | OTP Generator
    |--------------------------------------------------------------------------
    |
    | Default OTP generator class used by the system.
    |
    */

    'generator' => App\Services\OTP\Generators\NumericOtpGenerator::class,

    /*
    |--------------------------------------------------------------------------
    | OTP Bypass Verification
    |--------------------------------------------------------------------------
    |
    | Useful for local development or automated testing.
    |
    */

    'bypass_verification' => env('OTP_BYPASS_VERIFICATION', false),

    /*
    |--------------------------------------------------------------------------
    | OTP Resend Cooldown
    |--------------------------------------------------------------------------
    |
    | Defines the minimum number of seconds a user must wait before requesting
    | another OTP for the same verification context (e.g. login, email verification,
    | password reset). This helps prevent OTP spamming and email/SMS flooding.
    |
    */
    'resend_cooldown_seconds' => env('OTP_RESEND_COOLDOWN_SECONDS', 120),

];