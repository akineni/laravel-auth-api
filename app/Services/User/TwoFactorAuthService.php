<?php

namespace App\Services\User;

use App\Enums\TwoFactorMethodEnum;
use App\Models\User;
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
        $this->ensureAuthenticatorNotAlreadyEnabled($user);

        $secret = $this->google2fa->generateSecretKey();

        $otpauthUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        // $qrCode = $this->generateQrCodeSvg($otpauthUrl);

        return [
            'secret' => $secret,
            'otpauth_url' => $otpauthUrl,
            // 'qr_code' => $qrCode,
            'qr_code_url' => route('user.two-fa.authenticator.qr-code', [
                'secret' => $secret,
            ]),
        ];
    }

    public function confirmAuthenticator(User $user, string $secret, string $code): array
    {
        $this->ensureAuthenticatorNotAlreadyEnabled($user);

        $isValid = $this->google2fa->verifyKey($secret, $code);

        if (!$isValid) {
            throw ValidationException::withMessages([
                'code' => ['The provided authenticator code is invalid.'],
            ]);
        }

        $recoveryCodes = $this->generateRecoveryCodes();

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

        return [
            'recovery_codes' => $recoveryCodes,
        ];
    }

    public function disableAuthenticator(User $user): bool
    {
        $this->ensureAuthenticatorEnabled($user);
        
        return $this->userRepository->update($user, [
            'two_fa' => false,
            'two_fa_method' => TwoFactorMethodEnum::DEFAULT->value,
            'two_fa_secret' => null,
            'two_fa_confirmed_at' => null,
            'two_fa_recovery_codes' => null,
            'two_fa_last_used_window' => null,
        ]);
    }

    public function regenerateRecoveryCodes(User $user): array
    {
        $this->ensureAuthenticatorEnabled($user);

        $recoveryCodes = $this->generateRecoveryCodes();

        $updated = $this->userRepository->update($user, [
            'two_fa_recovery_codes' => $recoveryCodes,
        ]);

        if (!$updated) {
            throw ValidationException::withMessages([
                'two_fa' => ['Failed to regenerate recovery codes. Please try again.'],
            ]);
        }

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

    private function ensureAuthenticatorNotAlreadyEnabled(User $user): void
    {
        if (
            $user->two_fa &&
            $user->two_fa_method === TwoFactorMethodEnum::AUTHENTICATOR_APP->value &&
            $user->two_fa_secret
        ) {
            throw ValidationException::withMessages([
                'two_fa' => ['Authenticator 2FA is already enabled for this account.'],
            ]);
        }
    }

    private function ensureAuthenticatorEnabled(User $user): void
    {
        if (
            !$user->two_fa ||
            $user->two_fa_method !== TwoFactorMethodEnum::AUTHENTICATOR_APP->value ||
            !$user->two_fa_secret
        ) {
            throw ValidationException::withMessages([
                'two_fa' => ['Authenticator 2FA is not enabled for this account.'],
            ]);
        }
    }
}