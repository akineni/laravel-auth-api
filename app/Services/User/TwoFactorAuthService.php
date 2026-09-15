<?php

namespace App\Services\User;

use App\Enums\TwoFactorMethodEnum;
use App\Models\User;
use App\Notifications\RecoveryCodesRegeneratedNotification;
use App\Notifications\TwoFactorDisabledNotification;
use App\Notifications\TwoFactorEnabledNotification;
use App\Repositories\Contracts\UserRepositoryInterface;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorAuthService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly Google2FA $google2fa
    ) {}

    public function renderAuthenticatorQrCode(User $user, string $secret): string
    {
        $otpauthUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        return $this->generateQrCodeSvg($otpauthUrl);
    }

    public function setupAuthenticator(User $user): array
    {
        $this->ensureAuthenticatorNotConfigured($user);

        $secret = $this->google2fa->generateSecretKey();

        $otpauthUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        return [
            'secret' => $secret,
            'otpauth_url' => $otpauthUrl,
            'qr_code_url' => route('user.two-fa.authenticator.qr-code', [
                'secret' => $secret,
            ]),
        ];
    }

    public function confirmAuthenticator(User $user, string $secret, string $code): array
    {
        $this->ensureAuthenticatorNotConfigured($user);

        $isValid = $this->google2fa->verifyKey($secret, $code);

        if (!$isValid) {
            throw ValidationException::withMessages([
                'code' => ['The provided authenticator code is invalid.'],
            ]);
        }

        $recoveryCodes = $this->generateRecoveryCodes();

        // Setting up an authenticator app also switches the active medium to
        // it and turns two-factor on, matching the previous single-step flow.
        $updated = $this->userRepository->update($user, [
            'two_fa' => true,
            'two_fa_method' => TwoFactorMethodEnum::AUTHENTICATOR_APP->value,
            'two_fa_secret' => $secret,
            'two_fa_confirmed_at' => now(),
            'two_fa_recovery_codes' => $recoveryCodes,
            'two_fa_last_used_window' => null,
        ]);

        if (!$updated) {
            throw ValidationException::withMessages([
                'two_fa' => ['Failed to enable authenticator 2FA. Please try again.'],
            ]);
        }

        $user->notify(new TwoFactorEnabledNotification());

        return [
            'recovery_codes' => $recoveryCodes,
        ];
    }

    /**
     * Turn two-factor authentication on, using whichever medium is
     * currently selected (email by default, or a previously configured
     * authenticator app).
     */
    public function enable(User $user): bool
    {
        $this->ensureNotAlreadyEnabled($user);

        $updated = $this->userRepository->update($user, [
            'two_fa' => true,
        ]);

        if ($updated) {
            $user->notify(new TwoFactorEnabledNotification());
        }

        return $updated;
    }

    /**
     * Turn two-factor authentication off. The active medium and any
     * authenticator app configuration are left untouched, so re-enabling
     * later doesn't require setting anything up again.
     */
    public function disable(User $user): bool
    {
        $this->ensureEnabled($user);

        $updated = $this->userRepository->update($user, [
            'two_fa' => false,
        ]);

        if ($updated) {
            $user->notify(new TwoFactorDisabledNotification());
        }

        return $updated;
    }

    /**
     * Switch which medium two-factor uses, independently of whether it's
     * currently on or off. Switching to the authenticator app requires one
     * to already be set up; switching to email is always available.
     */
    public function switchMethod(User $user, string $method): bool
    {
        $enum = TwoFactorMethodEnum::tryFrom($method);

        if (!$enum) {
            throw ValidationException::withMessages([
                'method' => ['Invalid two-factor method.'],
            ]);
        }

        if ($enum === TwoFactorMethodEnum::AUTHENTICATOR_APP) {
            $this->ensureAuthenticatorConfigured($user);
        }

        return $this->userRepository->update($user, [
            'two_fa_method' => $enum->value,
        ]);
    }

    public function regenerateRecoveryCodes(User $user): array
    {
        $this->ensureAuthenticatorConfigured($user);

        $recoveryCodes = $this->generateRecoveryCodes();

        $updated = $this->userRepository->update($user, [
            'two_fa_recovery_codes' => $recoveryCodes,
        ]);

        if (!$updated) {
            throw ValidationException::withMessages([
                'two_fa' => ['Failed to regenerate recovery codes. Please try again.'],
            ]);
        }

        $user->notify(new RecoveryCodesRegeneratedNotification());

        return [
            'recovery_codes' => $recoveryCodes,
        ];
    }

    protected function generateQrCodeSvg(string $otpauthUrl): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle(220),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);

        return $writer->writeString($otpauthUrl);
    }

    protected function generateRecoveryCodes(int $count = 8): array
    {
        return collect(range(1, $count))
            ->map(fn () => Str::upper(Str::random(10)))
            ->values()
            ->toArray();
    }

    private function ensureAuthenticatorNotConfigured(User $user): void
    {
        if ($user->two_fa_secret) {
            throw ValidationException::withMessages([
                'two_fa' => ['An authenticator app is already set up for this account.'],
            ]);
        }
    }

    private function ensureAuthenticatorConfigured(User $user): void
    {
        if (!$user->two_fa_secret) {
            throw ValidationException::withMessages([
                'two_fa' => ['No authenticator app has been set up for this account yet.'],
            ]);
        }
    }

    private function ensureNotAlreadyEnabled(User $user): void
    {
        if ($user->two_fa) {
            throw ValidationException::withMessages([
                'two_fa' => ['Two-factor authentication is already enabled for this account.'],
            ]);
        }
    }

    private function ensureEnabled(User $user): void
    {
        if (!$user->two_fa) {
            throw ValidationException::withMessages([
                'two_fa' => ['Two-factor authentication is not enabled for this account.'],
            ]);
        }
    }
}
