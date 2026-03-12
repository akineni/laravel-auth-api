<?php

namespace App\Services\OTP;

use App\Models\OneTimePassword;
use App\Repositories\Contracts\OneTimePasswordRepositoryInterface;

class VerifyOtpService
{
    public function __construct(
        protected OneTimePasswordRepositoryInterface $oneTimePasswordRepository
    ) {}

    public function verify(string $challengeToken): array
    {
        $challenge = $this->oneTimePasswordRepository->findActiveByChallengeToken($challengeToken);

        if (!$challenge) {
            return [
                'success' => false,
                'message' => 'Invalid or expired OTP challenge.',
                'data' => null,
            ];
        }

        if ($this->oneTimePasswordRepository->isChallengeExpired($challenge)) {
            return [
                'success' => false,
                'message' => 'OTP has expired. Please request a new one.',
                'data' => null,
            ];
        }

        return [
            'success' => true,
            'message' => 'OTP challenge is valid.',
            'data' => $challenge,
        ];
    }

    public function verifyCode(string $challengeToken, string $otp): array
    {
        $challenge = $this->oneTimePasswordRepository->findActiveByChallengeToken($challengeToken);

        if (!$challenge) {
            return [
                'success' => false,
                'message' => 'Invalid or expired OTP challenge.',
                'data' => null,
            ];
        }

        if ($this->oneTimePasswordRepository->isChallengeExpired($challenge)) {
            return [
                'success' => false,
                'message' => 'OTP has expired. Please request a new one.',
                'data' => null,
            ];
        }

        if (!$this->oneTimePasswordRepository->challengeMatches($challenge, $otp)) {
            return [
                'success' => false,
                'message' => 'Invalid OTP.',
                'data' => null,
            ];
        }

        $this->oneTimePasswordRepository->markChallengeVerified($challenge);

        return [
            'success' => true,
            'message' => 'OTP verified successfully.',
            'data' => $challenge,
        ];
    }
}