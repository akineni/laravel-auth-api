<?php

namespace App\Http\Controllers\v1\Auth;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\ExchangeSsoCodeRequest;
use App\Http\Requests\GetSsoAuthUrlRequest;
use App\Services\Auth\SSO\SsoService;
use Illuminate\Http\Request;

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
        $url = $this->ssoService->getAuthUrl(
            $provider,
            $request->validated('success_url')
        );

        return ApiResponse::success('Redirect user to provider login', [
            'auth_url' => $url,
        ]);
    }

    /**
     * Handle SSO provider callback
     *
     * @group SSO Authentication
     */
    public function handleCallback(Request $request, string $provider)
    {
        [$code, $successUrl] = $this->ssoService->handleCallback(
            $provider,
            $request->query('state')
        );

        $separator = str_contains($successUrl, '#') ? '&' : '#';
        $redirectUrl = $successUrl . $separator . 'code=' . urlencode($code);

        return redirect()->away($redirectUrl);
    }

    /**
     * Exchange SSO code for access token
     *
     * @group SSO Authentication
     */
    public function exchangeCode(ExchangeSsoCodeRequest $request)
    {
        $result = $this->ssoService->exchangeCode(
            $request->validated('code')
        );

        return ApiResponse::success(
            'Sign-in successful and token issued',
            $result
        );
    }
}