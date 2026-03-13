<?php

namespace App\Services\OTP;

use App\Data\Auth\OtpChallengeData;
use App\Enums\OtpContextEnum;
use App\Exceptions\Auth\UnsupportedOtpChannelException;
use App\Models\User;
use App\Repositories\Contracts\OneTimePasswordRepositoryInterface;
use App\Services\OTP\Contracts\OtpChannelInterface;
use App\Services\OTP\Contracts\OtpGeneratorInterface;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class SendOtpService
{
    /**
     * @param array<string, OtpChannelInterface> $channels
     */
    public function __construct(
        protected OtpGeneratorInterface $generator,
        protected OneTimePasswordRepositoryInterface $oneTimePasswordRepository,
        protected array $channels = [],
    ) {}

    public function send(User $user, string $channelName, OtpContextEnum $context): OtpChallengeData
    {
        $channel = $this->resolveChannel($channelName);

        if (!$channel->supports($user)) {
            throw new UnsupportedOtpChannelException(
                "The {$channelName} channel is not supported for this user."
            );
        }

        $otp = $this->generator->generate();
        $expiresIn = (int) config('otp.expiry_minutes', 5) * 60;
        $expiresAt = now()->addSeconds($expiresIn);

        $challenge = $this->oneTimePasswordRepository->createChallenge(
            user: $user,
            code: $otp,
            expiresAt: $expiresAt,
            channel: $channel->name(),
            context: $context->value,
        );

        $this->logOtp($otp, $user, $context);
        $channel->send($user, $otp, $context);

        return new OtpChallengeData(
            otpRequired: true,
            destination: $this->resolveMaskedDestination($user, $channelName) ?? '',
            challengeToken: $challenge->challenge_token,
            expiresIn: $expiresIn,
        );
    }

    protected function resolveChannel(string $channelName): OtpChannelInterface
    {
        if (!isset($this->channels[$channelName])) {
            throw new InvalidArgumentException("Unsupported OTP channel: {$channelName}");
        }

        return $this->channels[$channelName];
    }

    protected function resolveMaskedDestination(User $user, string $channelName): ?string
    {
        return match ($channelName) {
            'email' => $user->email
                ? \Str::mask($user->email, '*', 1, strpos($user->email, '@') - 2)
                : null,

            'sms' => $user->phone_number
                ? \Str::mask($user->phone_number, '*', 3, strlen($user->phone_number) - 6)
                : null,

            default => null,
        };
    }

    protected function logOtp(string $otp, ?User $user = null, $context = null): void
    {
        if (!app()->environment('local')) {
            return;
        }

        Log::info('OTP generated', [
            'otp' => $otp,
            'user_id' => $user?->id,
            'context' => $context?->value,
        ]);
    }
}