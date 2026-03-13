<?php

namespace App\Services\Auth\SSO;

use App\Exceptions\Auth\Sso\{SsoInvalidStateException, SsoInvalidCodeException};
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Auth\TokenService;
use App\Traits\CompletesLogin;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SsoService
{
    use CompletesLogin;

    protected int $stateTtlMinutes = 10;
    protected int $codeTtlMinutes = 5;

    public function __construct(
        protected TokenService $tokenService,
        protected UserRepositoryInterface $userRepository
    ) {}

    /**
     * Generate the provider authentication URL.
     */
    public function getAuthUrl(string $provider, string $successUrl): string
    {
        $state = $this->storeState($provider, $successUrl);

        $ssoProvider = SsoProviderFactory::make($provider);

        return $ssoProvider
            ->redirect($state)
            ->getTargetUrl();
    }

    /**
     * Handle provider callback and return exchange code plus success URL.
     *
     * @return array{0: string, 1: string}
     */
    public function handleCallback(string $provider, ?string $state): array
    {
        if (!$state) {
            throw new SsoInvalidStateException('Missing state in callback.');
        }

        $successUrl = $this->consumeState($provider, $state);

        if (!$successUrl) {
            throw new SsoInvalidStateException('Invalid or expired state.');
        }

        $ssoProvider = SsoProviderFactory::make($provider);
        $user = $ssoProvider->handleCallback();

        $code = $this->generateExchangeCode($user);

        return [$code, $successUrl];
    }

    /**
     * Exchange one-time code for token response.
     */
    public function exchangeCode(string $code): array
    {
        $user = $this->consumeExchangeCode($code);

        if (!$user) {
            throw new SsoInvalidCodeException('Invalid or expired code.');
        }

        $token = $this->generateLoginToken($user);

        return $this->formatTokenDataResponse($user, $token);
    }

    protected function storeState(string $provider, string $successUrl): string
    {
        $state = Str::random(64);

        Cache::put(
            $this->stateCacheKey($provider, $state),
            ['success_url' => $successUrl],
            now()->addMinutes($this->stateTtlMinutes)
        );

        return $state;
    }

    protected function consumeState(string $provider, string $state): ?string
    {
        $payload = Cache::pull($this->stateCacheKey($provider, $state));

        if (!is_array($payload) || empty($payload['success_url'])) {
            return null;
        }

        return $payload['success_url'];
    }

    protected function generateExchangeCode(User $user): string
    {
        $code = Str::random(64);

        Cache::put(
            $this->codeCacheKey($code),
            ['user_id' => $user->id],
            now()->addMinutes($this->codeTtlMinutes)
        );

        return $code;
    }

    protected function consumeExchangeCode(string $code): ?User
    {
        $payload = Cache::pull($this->codeCacheKey($code));

        if (!is_array($payload) || empty($payload['user_id'])) {
            return null;
        }

        return $this->userRepository->findById($payload['user_id']);
    }

    protected function stateCacheKey(string $provider, string $state): string
    {
        return "sso_state:{$provider}:{$state}";
    }

    protected function codeCacheKey(string $code): string
    {
        return "sso_code:{$code}";
    }
}