<?php

namespace App\Http\Controllers\v1\Auth;

use App\Exceptions\SsoInvalidCodeException;
use App\Exceptions\UnsupportedSsoProviderException;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\ExchangeSsoCodeRequest;
use App\Http\Requests\GetSsoAuthUrlRequest;
use App\Services\Auth\SSO\SsoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SsoController extends Controller
{
    public function __construct(
        protected SsoService $ssoService
    ) {}

    /**
     * Get SSO provider authentication URL
     *
     * @group SSO Authentication
     */
    public function getAuthUrl(GetSsoAuthUrlRequest $request, string $provider)
    {
        try {
            $url = $this->ssoService->getAuthUrl(
                $provider,
                $request->validated('success_url')
            );

            return ApiResponse::success('Redirect user to provider login', [
                'auth_url' => $url,
            ]);
        } catch (UnsupportedSsoProviderException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Throwable $e) {
            Log::error('Failed to generate SSO auth URL.', [
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]);

            return ApiResponse::error('Failed to generate authentication URL.', 500);
        }
    }

    /**
     * Handle SSO provider callback
     *
     * @group SSO Authentication
     */
    public function handleCallback(Request $request, string $provider)
    {
        try {
            [$code, $successUrl] = $this->ssoService->handleCallback(
                $provider,
                $request->query('state')
            );

            $separator = str_contains($successUrl, '#') ? '&' : '#';
            $redirectUrl = $successUrl . $separator . 'code=' . urlencode($code);

            return redirect()->away($redirectUrl);
        } catch (UnsupportedSsoProviderException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Throwable $e) {
            Log::error('Failed to handle SSO callback.', [
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]);

            return ApiResponse::error('Failed to handle SSO callback.', 500);
        }
    }

    /**
     * Exchange SSO code for access token
     *
     * @group SSO Authentication
     */
    public function exchangeCode(ExchangeSsoCodeRequest $request)
    {
        try {
            $result = $this->ssoService->exchangeCode(
                $request->validated('code')
            );

            return ApiResponse::success(
                'Sign-in successful and token issued',
                $result
            );
        } catch (SsoInvalidCodeException $e) {
            return ApiResponse::error($e->getMessage(), 401);
        } catch (\Throwable $e) {
            Log::error('Failed to exchange SSO code.', [
                'error' => $e->getMessage(),
            ]);

            return ApiResponse::error('Failed to exchange SSO code.', 500);
        }
    }
}