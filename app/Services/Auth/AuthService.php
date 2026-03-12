<?php

namespace App\Services\Auth;

use App\Enums\OtpContextEnum;
use App\Enums\UserStatusEnum;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use App\Repositories\Contracts\{
    OneTimePasswordRepositoryInterface,
    PasswordResetTokenRepositoryInterface,
    UserRepositoryInterface
};
use App\Traits\CompletesLogin;
use App\Services\OTP\SendOtpService;
use App\Services\OTP\VerifyOtpService;
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

    public function register(array $data): array
    {
        $user = $this->userRepository->create(
            collect($data)->except('password_confirmation')->toArray()
        );

        $response = $this->sendOtpService->send(
            user: $user,
            channelName: 'email',
            context: OtpContextEnum::EMAIL_VERIFICATION
        );

        if (!$response['success']) {
            return $response;
        }

        $response['message'] = 'Account created successfully. Please verify your email address with the OTP sent to you.';

        return $response;
    }

    public function login(array $credentials): array
    {
        $user = $this->userRepository->findByEmail($credentials['email']);

        if (!$user) {
            return $this->handleFailedLogin(null);
        }

        if ($response = $this->validateUserStatus($user)) {
            return $response;
        }

        if (!$this->checkCredentials($user, $credentials['password'])) {
            return $this->handleFailedLogin($user);
        }

        if ($this->isLocked($user)) {
            return [
                'success' => false,
                'message' => 'Account locked. Try again later.',
            ];
        }
        
        $this->userRepository->resetFailedLoginAttempts($user);

        if ($user->two_fa) {
            $result = $this->sendOtpService->send(
                user: $user,
                channelName: 'email',
                context: OtpContextEnum::LOGIN
            );

            if (!$result['success']) {
                return $result;
            }

            return [
                'success' => true,
                'message' => 'OTP sent to your email',
                'data' => $result['data']
            ];
        }

        return $this->completeLogin($user);
    }

    public function verifyOtpAndHandleChallenge(string $challengeToken, string $otp): array
    {
        $response = $this->verifyOtpService->verifyCode($challengeToken, $otp);

        if (!$response['success']) {
            return $response;
        }

        /** @var \App\Models\OneTimePassword $challenge */
        $challenge = $response['data'];
        $user = $challenge->user;

        return match ($challenge->context) {
            OtpContextEnum::LOGIN->value => $this->completeLogin($user),

            OtpContextEnum::EMAIL_VERIFICATION->value => $this->completeEmailVerification($user),

            OtpContextEnum::PASSWORD_RESET->value => [
                'success' => true,
                'message' => 'OTP verified successfully. You may now reset your password.',
                'data' => null,
            ],

            default => [
                'success' => false,
                'message' => 'Unsupported OTP context.',
                'data' => null,
            ],
        };
    }

    public function forgotPassword(string $email): array
    {
        $user = $this->userRepository->findByEmail($email);

        if ($user) {
            $this->sendPasswordResetLink($user);
        }

        return [
            'success' => true,
            'message' => 'If an account with that email exists, a password reset link has been sent.',
            'data' => null,
        ];
    }

    public function resetPassword(string $email, string $token, string $password): array
    {
        $validation = $this->validateResetRequest($email, $token);
        $user = $validation['user'] ?? null;

        if (!$validation['success']) {
            return $validation;
        }

        $this->userRepository->updatePassword($user, $password, false);
        $this->passwordResetRepository->delete($user);

        return [
            'success' => true,
            'message' => 'Password reset successful. You can now log in.',
        ];
    }

    public function resendOtp(string $challengeToken): array
    {
        $challenge = $this->oneTimePasswordRepository->findActiveByChallengeToken($challengeToken);

        if (!$challenge) {
            return [
                'success' => false,
                'message' => 'Invalid or expired OTP challenge.',
                'data' => null,
            ];
        }

        $user = $challenge->user;
        $context = \App\Enums\OtpContextEnum::from($challenge->context);

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

        return [
            'success' => true,
            'message' => 'Token refreshed successfully.',
            'data' => $this->formatTokenDataResponse($user, $token)
        ];
    }

    public function logout(): void
    {
        /** @var \Illuminate\Contracts\Auth\Guard|\Illuminate\Contracts\Auth\StatefulGuard $auth */
        $auth = auth();
        $auth->logout();
    }

    protected function validateResetRequest(string $email, string $token): array
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            return [
                'success' => false,
                'message' => 'Invalid user.',
                'user' => null,
            ];
        }

        if (!$this->passwordResetRepository->exists($user, $token)) {
            return [
                'success' => false,
                'message' => 'Invalid or expired reset token.',
                'user' => null,
            ];
        }

        return [
            'success' => true,
            'message' => 'Valid reset request.',
            'user' => $user,
        ];
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

    protected function handleFailedLogin(?User $user): array
    {
        if ($user) {
            $this->userRepository->incrementFailedLogins($user);

            if (($user->failed_logins + 1) >= self::MAX_FAILED_ATTEMPTS) {
                $this->userRepository->lockUntil($user, now()->addMinutes(self::LOCKOUT_MINUTES));
            }
        }

        return [
            'success' => false,
            'message' => 'Invalid credentials',
        ];
    }

    protected function isLocked(User $user): bool
    {
        return $user->locked_until && now()->lt($user->locked_until);
    }

    protected function validateUserStatus(User $user): ?array
    {
        if ($user->status !== UserStatusEnum::ACTIVE->value) {
            return [
                'success' => false,
                'message' => 'Account is inactive. Please contact support.',
            ];
        }

        return null;
    }

    protected function completeEmailVerification(User $user): array
    {
        $this->userRepository->verifyEmailAndActivate($user);

        return [
            'success' => true,
            'message' => 'Email verified successfully.'
        ];
    }
}
