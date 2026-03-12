<?php

namespace App\Services\OTP\Contracts;

use App\Enums\OtpContextEnum;
use App\Models\User;

interface OtpChannelInterface
{
    /**
     * Return the unique channel name.
     *
     * Examples: email, sms
     */
    public function name(): string;

    /**
     * Determine whether this channel can send OTP to the given user.
     */
    public function supports(User $user): bool;

    /**
     * Deliver the OTP to the given user.
     */
    public function send(User $user, string $otp, OtpContextEnum $context): void;
}