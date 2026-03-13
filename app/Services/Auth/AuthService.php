<?php

namespace App\Services\Auth;

use App\Data\Auth\{OtpChallengeData, AuthFlowResponseData};
use App\Enums\{OtpContextEnum, UserStatusEnum};
use App\Exceptions\Auth\{
    AccountLockedException,
    InactiveAccountException,
    InvalidCredentialsException,
    InvalidOtpChallengeException,
    InvalidResetTokenException,
    UnsupportedOtpContextException
};
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use App\Repositories\Contracts\{
    OneTimePasswordRepositoryInterface,
    PasswordResetTokenRepositoryInterface,
    UserRepositoryInterface
};
use App\Services\OTP\{SendOtpService, VerifyOtpService};
use App\Traits\CompletesLogin;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    use CompletesLogin;

    const MAX_FAILED_ATTEMPTS = 5;
    const LOCKOUT_MINUTES = 15;

    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordResetTokenRepositoryInterface $passwordResetRepository,
        private OneTimePasswordRepositoryInterface $oneTimePasswordRepository,
        private SendOtpService $sendOtpService,
        private VerifyOtpService $verifyOtpService,
        private TokenService $tokenService,
    ) {}

    public function register(array $data): OtpChallengeData
    {
        $user = $this->userRepository->create(
            collect($data)->except('password_confirmation')->toArray()
        );

        return $this->sendOtpService->send(
            user: $user,
            channelName: 'email',
            context: OtpContextEnum::EMAIL_VERIFICATION
        );
    }

    public function login(array $credentials): AuthFlowResponseData
    {
        $user = $this->userRepository->findByEmail($credentials['email']);

        if (!$user) {
            $this->handleFailedLogin();
        }

        $this->ensureUserIsNotLocked($user);
        $this->ensureUserIsActive($user);

        if (!$this->checkCredentials($user, $credentials['password'])) {
            $this->handleFailedLogin($user);
        }

        $this->userRepository->resetFailedLoginAttempts($user);

        if ($user->two_fa) {
            $otpChallengeData = $this->sendOtpService->send(
                user: $user,
                channelName: 'email',
                context: OtpContextEnum::LOGIN
            );

            return AuthFlowResponseData::otpRequired($otpChallengeData);
        }

        return AuthFlowResponseData::authenticated(
            $this->finalizeLogin($user)
        );
    }

    public function verifyOtpAndHandleChallenge(string $challengeToken, string $otp): AuthFlowResponseData
    {
        $challenge = $this->verifyOtpService->verifyCode($challengeToken, $otp);
        $user = $challenge->user;

        return match ($challenge->context) {
            OtpContextEnum::LOGIN->value => AuthFlowResponseData::authenticated(
                $this->finalizeLogin($user)
            ),

            OtpContextEnum::EMAIL_VERIFICATION->value => $this->completeEmailVerification($user),

            OtpContextEnum::PASSWORD_RESET->value => AuthFlowResponseData::passwordResetVerified(),

            default => throw new UnsupportedOtpContextException(),
        };
    }

    public function forgotPassword(string $email): void
    {
        $user = $this->userRepository->findByEmail($email);

        if ($user) {
            $this->sendPasswordResetLink($user);
        }
    }

    public function resetPassword(string $email, string $token, string $password): void
    {
        $user = $this->validateResetRequest($email, $token);

        $this->userRepository->updatePassword($user, $password, false);
        $this->passwordResetRepository->delete($user);
    }

    public function resendOtp(string $challengeToken): OtpChallengeData
    {
        $challenge = $this->oneTimePasswordRepository->findActiveByChallengeToken($challengeToken);

        if (!$challenge) {
            throw new InvalidOtpChallengeException();
        }

        $user = $challenge->user;
        $context = OtpContextEnum::from($challenge->context);

        return $this->sendOtpService->send(
            user: $user,
            channelName: $challenge->channel,
            context: $context
        );
    }

    public function refresh(): array
    {
        $token = $this->tokenService->refresh();

        /** @var \Illuminate\Contracts\Auth\Guard|\Illuminate\Contracts\Auth\StatefulGuard $auth */
        $auth = auth();
        $user = $auth->user();

        return $this->formatTokenDataResponse($user, $token);
    }

    public function logout(): void
    {
        /** @var \Illuminate\Contracts\Auth\Guard|\Illuminate\Contracts\Auth\StatefulGuard $auth */
        $auth = auth();
        $auth->logout();
    }

    protected function validateResetRequest(string $email, string $token): User
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            throw new InvalidResetTokenException('Invalid user.');
        }

        if (!$this->passwordResetRepository->exists($user, $token)) {
            throw new InvalidResetTokenException();
        }

        return $user;
    }

    protected function sendPasswordResetLink(User $user): void
    {
        $token = $this->passwordResetRepository->create($user);
        $callbackUrl = config('frontend.reset_password_url');

        $separator = str_contains($callbackUrl, '?') ? '&' : '?';

        $resetUrl = $callbackUrl . $separator . http_build_query([
            'token' => $token,
            'email' => $user->email,
        ]);

        $user->notify(new ResetPasswordNotification($resetUrl));
    }

    protected function checkCredentials(?User $user, string $password): bool
    {
        return $user && Hash::check($password, $user->password);
    }

    protected function handleFailedLogin(?User $user = null): never
    {
        if ($user) {
            $this->userRepository->incrementFailedLogins($user);

            if (($user->failed_logins + 1) >= self::MAX_FAILED_ATTEMPTS) {
                $this->userRepository->lockUntil($user, now()->addMinutes(self::LOCKOUT_MINUTES));

                throw new AccountLockedException('Account locked due to too many failed login attempts. Try again later.');
            }
        }

        throw new InvalidCredentialsException();
    }

    protected function ensureUserIsNotLocked(User $user): void
    {
        if ($user->locked_until && now()->lt($user->locked_until)) {
            throw new AccountLockedException();
        }
    }

    protected function ensureUserIsActive(User $user): void
    {
        if ($user->status !== UserStatusEnum::ACTIVE->value) {
            throw new InactiveAccountException();
        }
    }

    protected function completeEmailVerification(User $user): AuthFlowResponseData
    {
        $this->userRepository->verifyEmailAndActivate($user);

        return AuthFlowResponseData::emailVerified();
    }
}