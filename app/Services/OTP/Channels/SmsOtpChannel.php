<?php

namespace App\Services\OTP\Channels;

use App\Enums\OtpContextEnum;
use App\Models\User;
use App\Services\OTP\Contracts\OtpChannelInterface;
use App\Services\SMS\SmsService;

class SmsOtpChannel implements OtpChannelInterface
{
    public function __construct(
        protected SmsService $smsService
    ) {}

    public function name(): string
    {
        return 'sms';
    }

    public function supports(User $user): bool
    {
        return filled($user->phone_number);
    }

    public function send(User $user, string $otp, OtpContextEnum $context): void
    {
        $message = match ($context) {
            OtpContextEnum::LOGIN => "Your login verification code is {$otp}.",
            OtpContextEnum::EMAIL_VERIFICATION => "Your account verification code is {$otp}.",
            OtpContextEnum::PHONE_VERIFICATION => "Your phone verification code is {$otp}.",
            OtpContextEnum::PASSWORD_RESET => "Your password reset code is {$otp}.",
            default => "Your verification code is {$otp}.",
        };

        $this->smsService->send($user->phone_number, $message);
    }
}