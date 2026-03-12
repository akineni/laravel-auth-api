<?php

namespace App\Http\Controllers\v1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\{
    ForgotPasswordRequest,
    LoginRequest,
    RegisterRequest,
    ResendOtpRequest,
    ResetPasswordRequest,
    VerifyOtpRequest,
};
use App\Helpers\ApiResponse;
use App\Services\Auth\AuthService;
use App\Services\Auth\TokenService;

class AuthController extends Controller
{
    protected $authService;
    protected TokenService $tokenService;

    public function __construct(AuthService $authService, TokenService $tokenService)
    {
        $this->authService = $authService;
        $this->tokenService = $tokenService;
    }

    /**
     * Register User
     *
     * Creates a new user account and issues an access token.
     *
     * @group Authentication
     */
    public function register(RegisterRequest $request)
    {
        $result = $this->authService->register($request->validated());

        return $result['success']
            ? ApiResponse::success($result['message'], $result['data'] ?? null, 201)
            : ApiResponse::error($result['message'], 422, $result['data'] ?? null);
    }

    /**
     * Login User
     *
     * Authenticates a user using email and password. If the credentials are valid,
     * an OTP may be sent to the user for verification depending on the authentication flow.
     *
     * @group Authentication
     */
    public function login(LoginRequest $request)
    {
        $result = $this->authService->login($request->validated());

        if (!$result['success']) {
            return ApiResponse::error($result['message'], 401, $result['data'] ?? null);
        }

        return ApiResponse::success($result['message'], $result['data'] ?? null);
    }

    /**
     * Verify OTP
     *
     * Verifies a one-time password sent to the user during authentication
     * or other verification processes.
     *
     * @group Authentication
     */
    public function verifyOtp(VerifyOtpRequest $request)
    {
        $validated = $request->validated();

        $response = $this->authService->verifyOtpAndHandleChallenge(
            $validated['challenge_token'],
            $validated['otp']
        );

        return $response['success']
            ? ApiResponse::success($response['message'], $response['data'] ?? null)
            : ApiResponse::error($response['message'], 401);
    }

    /**
     * Forgot Password
     *
     * Starts the password reset process by sending a reset link or token
     * to the user's email address.
     *
     * @group Password Reset
     */
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $validated = $request->validated();

        $result = $this->authService->forgotPassword($validated['email']);

        return $result['success']
            ? ApiResponse::success($result['message'], $result['data'] ?? null, 200)
            : ApiResponse::error($result['message'], 400, $result['data'] ?? null);
    }

    /**
     * Reset password
     *
     * Completes the password reset process by validating the reset token
     * and setting a new password for the user.
     *
     * @group Password Reset
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        $validated = $request->validated();

        $result = $this->authService->resetPassword(
            $validated['email'],
            $validated['token'],
            $validated['password']
        );

        return $result['success']
            ? ApiResponse::success($result['message'])
            : ApiResponse::error($result['message']);
    }

    /**
     * Resend OTP
     *
     * Resends a one-time password to the user if the previous OTP expired
     * or was not received.
     *
     * @group Authentication
     */
    public function resendOtp(ResendOtpRequest $request)
    {
        $validated = $request->validated();

        $result = $this->authService->resendOtp($validated['challenge_token']);

        return $result['success']
            ? ApiResponse::success($result['message'], $result['data'] ?? null)
            : ApiResponse::error($result['message'], 400, $result['data'] ?? null);
    }

    /**
     * Refresh Authentication Token
     *
     * Issues a new access token for an authenticated user whose current token
     * is close to expiration.
     *
     * @group Authentication
     * @authenticated
     */
    public function refreshToken()
    {
        $result = $this->authService->refresh();

        return $result['success']
            ? ApiResponse::success($result['message'], $result['data'])
            : ApiResponse::error($result['message'], 401);
    }

    /**
     * Logout User
     *
     * Logs out the currently authenticated user and invalidates their token.
     *
     * @group Authentication
     * @authenticated
     */
    public function logout()
    {
        $this->authService->logout();

        return ApiResponse::success('User logged out successfully');
    }
}