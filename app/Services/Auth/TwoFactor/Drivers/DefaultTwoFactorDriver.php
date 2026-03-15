<?php

namespace App\Services\Auth\TwoFactor\Drivers;

use App\Data\Auth\AuthFlowResponseData;
use App\Enums\OtpContextEnum;
use App\Enums\OtpMethodEnum;
use App\Enums\TwoFactorMethodEnum;
use App\Models\User;
use App\Services\Auth\TwoFactor\Contracts\TwoFactorDriverInterface;
use App\Services\OTP\SendOtpService;
use App\Services\OTP\VerifyOtpService;

class DefaultTwoFactorDriver implements TwoFactorDriverInterface
{
    public function __construct(
        private readonly SendOtpService $sendOtpService,
        private readonly VerifyOtpService $verifyOtpService
    ) {}

    /**
     * Determine if the driver supports the given 2FA method.
     */
    public function supports(string $method): bool
    {
        return $method === TwoFactorMethodEnum::DEFAULT->value;
    }

    /**
     * Initiate the default OTP challenge for the given context.
     */
    public function beginChallenge(User $user, OtpContextEnum $context): AuthFlowResponseData
    {
        $otpChallengeData = $this->sendOtpService->send(
            user: $user,
            method: OtpMethodEnum::OTP_EMAIL,
            context: $context
        );

        return AuthFlowResponseData::otpRequired(
            $otpChallengeData,
            message: 'OTP sent to your email.'
        );
    }

    /**
     * Verify the submitted OTP challenge code.
     */
    public function verify(
        User $user,
        string $code,
        OtpContextEnum $context,
        ?string $challengeToken = null
    ): bool {
        if (!$challengeToken) {
            return false;
        }

        $this->verifyOtpService->verifyCode(
            challengeToken: $challengeToken,
            otp: $code
        );

        return true;
    }
}