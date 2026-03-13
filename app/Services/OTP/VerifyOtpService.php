<?php

namespace App\Services\OTP;

use App\Exceptions\Auth\ExpiredOtpException;
use App\Exceptions\Auth\InvalidOtpChallengeException;
use App\Exceptions\Auth\OtpVerificationException;
use App\Models\OneTimePassword;
use App\Repositories\Contracts\OneTimePasswordRepositoryInterface;

class VerifyOtpService
{
    public function __construct(
        protected OneTimePasswordRepositoryInterface $oneTimePasswordRepository
    ) {}

    public function verifyCode(string $challengeToken, string $otp): OneTimePassword
    {
        $challenge = $this->oneTimePasswordRepository->findActiveByChallengeToken($challengeToken);

        if (!$challenge) {
            throw new InvalidOtpChallengeException();
        }

        if ($this->oneTimePasswordRepository->isChallengeExpired($challenge)) {
            throw new ExpiredOtpException();
        }

        if (!$this->oneTimePasswordRepository->challengeMatches($challenge, $otp)) {
            throw new OtpVerificationException('Invalid OTP.');
        }

        $this->oneTimePasswordRepository->markChallengeVerified($challenge);

        return $challenge;
    }
}