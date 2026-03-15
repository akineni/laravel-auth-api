<?php

namespace App\Services\Auth\TwoFactor\Contracts;

use App\Data\Auth\AuthFlowResponseData;
use App\Enums\OtpContextEnum;
use App\Models\User;

interface TwoFactorDriverInterface
{
    /**
     * Determine if the driver supports the given 2FA method.
     */
    public function supports(string $method): bool;

    /**
     * Initiate the second-factor challenge for the given context.
     */
    public function beginChallenge(User $user, OtpContextEnum $context): AuthFlowResponseData;

    /**
     * Verify the provided second-factor code for the given context.
     */
    public function verify(
        User $user,
        string $code,
        OtpContextEnum $context,
        ?string $challengeToken = null
    ): bool;
}