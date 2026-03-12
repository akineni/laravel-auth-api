<?php

namespace App\Services\OTP\Channels;

use App\Enums\OtpContextEnum;
use App\Mail\OtpMail;
use App\Models\User;
use App\Services\OTP\Contracts\OtpChannelInterface;
use Illuminate\Support\Facades\Mail;

class EmailOtpChannel implements OtpChannelInterface
{
    public function name(): string
    {
        return 'email';
    }

    public function supports(User $user): bool
    {
        return !empty($user->email);
    }

    public function send(User $user, string $otp, OtpContextEnum $context): void
    {
        Mail::to($user->email)->queue(
            new OtpMail($user->firstname, $otp, $context)
        );
    }
}