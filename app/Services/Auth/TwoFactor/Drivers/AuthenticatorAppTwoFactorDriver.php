<?php

namespace App\Services\Auth\TwoFactor\Drivers;

use App\Data\Auth\AuthFlowResponseData;
use App\Data\Auth\OtpChallengeData;
use App\Enums\OtpContextEnum;
use App\Enums\OtpMethodEnum;
use App\Enums\TwoFactorMethodEnum;
use App\Models\User;
use App\Repositories\Contracts\AuthChallengeRepositoryInterface;
use App\Services\Auth\TwoFactor\Contracts\TwoFactorDriverInterface;
use PragmaRX\Google2FA\Google2FA;

class AuthenticatorAppTwoFactorDriver implements TwoFactorDriverInterface
{
    public function __construct(
        private readonly Google2FA $google2fa,
        private readonly AuthChallengeRepositoryInterface $authChallengeRepository
    ) {}

    public function supports(string $method): bool
    {
        return $method === TwoFactorMethodEnum::AUTHENTICATOR_APP->value;
    }

    public function beginChallenge(User $user, OtpContextEnum $context): AuthFlowResponseData
    {
        $expiresIn = (int) config('otp.expiry_minutes', 5) * 60;
        $expiresAt = now()->addSeconds($expiresIn);

        $challenge = $this->authChallengeRepository->createChallenge(
            user: $user,
            code: null,
            expiresAt: $expiresAt,
            method: OtpMethodEnum::TOTP->value,
            context: $context->value,
        );

        return new AuthFlowResponseData(
            message: 'Enter the code from your authenticator app.',
            data: new OtpChallengeData(
                otpRequired: true,
                destination: null,
                challengeToken: $challenge->challenge_token,
                expiresIn: $expiresIn,
            )->toArray()
        );
    }

    public function verify(
        User $user,
        string $code,
        OtpContextEnum $context,
        ?string $challengeToken = null
    ): bool {
        if (
            !$user->two_fa ||
            $user->two_fa_method !== TwoFactorMethodEnum::AUTHENTICATOR_APP->value ||
            !$user->two_fa_secret
        ) {
            return false;
        }

        return $this->google2fa->verifyKey(
            $user->two_fa_secret,
            $code
        );
    }
}
