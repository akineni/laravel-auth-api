<?php

namespace App\Services\OTP;

use App\Data\Auth\OtpChallengeData;
use App\Enums\{OtpContextEnum, OtpMethodEnum};
use App\Exceptions\Auth\UnsupportedOtpMethodException;
use App\Models\User;
use App\Repositories\Contracts\AuthChallengeRepositoryInterface;
use App\Services\OTP\Contracts\{OtpChannelInterface, OtpGeneratorInterface};
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class SendOtpService
{
    /**
     * @param array<string, OtpChannelInterface> $methodHandlers
     */
    public function __construct(
        protected readonly OtpGeneratorInterface $generator,
        protected readonly AuthChallengeRepositoryInterface $authChallengeRepository,
        protected array $methodHandlers = [],
    ) {}

    public function send(User $user, OtpMethodEnum $method, OtpContextEnum $context): OtpChallengeData
    {
        $handler = $this->resolveMethodHandler($method);

        if (!$handler->supports($user)) {
            throw new UnsupportedOtpMethodException(
                "The {$method->value} method is not supported for this user."
            );
        }

        $otp = $this->generator->generate();
        $expiresIn = (int) config('otp.expiry_minutes', 5) * 60;
        $expiresAt = now()->addSeconds($expiresIn);

        $challenge = $this->authChallengeRepository->createChallenge(
            user: $user,
            code: $otp,
            expiresAt: $expiresAt,
            method: $method->value,
            context: $context->value,
        );

        $this->logOtp($otp, $user, $context, $method);
        $handler->send($user, $otp, $context);

        return new OtpChallengeData(
            otpRequired: true,
            destination: $this->resolveMaskedDestination($user, $method) ?? '',
            challengeToken: $challenge->challenge_token,
            expiresIn: $expiresIn,
        );
    }

    protected function resolveMethodHandler(OtpMethodEnum $method): OtpChannelInterface
    {
        if (!isset($this->methodHandlers[$method->value])) {
            throw new InvalidArgumentException("Unsupported OTP method: {$method->value}");
        }

        return $this->methodHandlers[$method->value];
    }

    protected function resolveMaskedDestination(User $user, OtpMethodEnum $method): ?string
    {
        return match ($method) {
            OtpMethodEnum::OTP_EMAIL => $user->email
                ? \Str::mask($user->email, '*', 1, strpos($user->email, '@') - 2)
                : null,

            OtpMethodEnum::OTP_SMS => $user->phone_number
                ? \Str::mask($user->phone_number, '*', 3, strlen($user->phone_number) - 6)
                : null,

            default => null,
        };
    }

    protected function logOtp(
        string $otp,
        ?User $user = null,
        ?OtpContextEnum $context = null,
        ?OtpMethodEnum $method = null
    ): void {
        if (!app()->environment('local')) {
            return;
        }

        Log::info('OTP generated', [
            'otp' => $otp,
            'user_id' => $user?->id,
            'context' => $context?->value,
            'method' => $method?->value,
        ]);
    }
}