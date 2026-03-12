<?php

namespace App\Services\OTP\Channels;

use App\Enums\OtpContextEnum;
use App\Models\User;
use App\Services\OTP\Contracts\OtpChannelInterface;

class SmsOtpChannel implements OtpChannelInterface
{
    public function name(): string
    {
        return 'sms';
    }

    public function supports(User $user): bool
    {
        return !empty($user->phone_number);
    }

    public function send(User $user, string $otp, OtpContextEnum $context): void
    {
        // Call your SMS provider here.
        // Example: $this->smsClient->send($user->phone_number, "Your OTP is {$otp}");
    }
}