<?php

namespace App\Services\OTP;

use App\Enums\OtpMethodEnum;
use App\Exceptions\Auth\ExpiredOtpException;
use App\Exceptions\Auth\InvalidOtpChallengeException;
use App\Exceptions\Auth\OtpVerificationException;
use App\Models\AuthChallenge;
use App\Repositories\Contracts\AuthChallengeRepositoryInterface;
use PragmaRX\Google2FA\Google2FA;

class VerifyOtpService
{
    public function __construct(
        protected readonly AuthChallengeRepositoryInterface $authChallengeRepository,
        protected Google2FA $google2fa,
    ) {}

    public function verifyCode(string $challengeToken, string $otp): AuthChallenge
    {
        $challenge = $this->authChallengeRepository->findActiveByChallengeToken($challengeToken);

        if (!$challenge) {
            throw new InvalidOtpChallengeException();
        }

        if ($this->authChallengeRepository->isChallengeExpired($challenge)) {
            throw new ExpiredOtpException();
        }

        $method = OtpMethodEnum::from($challenge->method);

        $isValid = match ($method) {
            OtpMethodEnum::OTP_EMAIL,
            OtpMethodEnum::OTP_SMS => $this->authChallengeRepository->challengeMatches($challenge, $otp),

            OtpMethodEnum::TOTP => $this->verifyTotpChallenge($challenge, $otp),
        };

        if (!$isValid) {
            throw new OtpVerificationException('Invalid OTP.');
        }

        $this->authChallengeRepository->markChallengeVerified($challenge);

        return $challenge;
    }

    protected function verifyTotpChallenge(AuthChallenge $challenge, string $otp): bool
    {
        $user = $challenge->user;

        if (!$user || !$user->two_fa_secret) {
            return false;
        }

        return $this->google2fa->verifyKey($user->two_fa_secret, $otp);
    }
}