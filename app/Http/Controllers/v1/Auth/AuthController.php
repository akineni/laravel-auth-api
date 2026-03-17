<?php

namespace App\Http\Controllers\v1\Auth;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\{
    ForgotPasswordRequest,
    LoginRequest,
    RegisterRequest,
    ResendOtpRequest,
    ResetPasswordRequest,
    VerifyOtpRequest,
};
use App\Services\Auth\AuthService;

class AuthController extends Controller
{
    public function __construct(protected AuthService $authService)
    {}

    /**
     * Register User
     *
     * Creates a new user account and issues an access token.
     *
     * @group Authentication
     */
    public function register(RegisterRequest $request)
    {
        $otpChallengeData = $this->authService->register($request->validated());

        return ApiResponse::success(
            'Account created successfully. Please verify your email address with the OTP sent to you.',
            $otpChallengeData,
            201
        );
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
        $loginResponseData = $this->authService->login($request->validated());

        return ApiResponse::success(
            $loginResponseData->message,
            $loginResponseData->data ?? null
        );
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

        $authFlowResponseData = $this->authService->verifyOtpAndHandleChallenge(
            $validated['challenge_token'],
            $validated['otp']
        );

        return ApiResponse::success(
            $authFlowResponseData->message,
            $authFlowResponseData->data ?? null
        );
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

        $this->authService->forgotPassword($validated['email']);

        return ApiResponse::success(
            'If an account with that email exists, a password reset link has been sent.'
        );
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

        $this->authService->resetPassword(
            $validated['email'],
            $validated['token'],
            $validated['password']
        );

        return ApiResponse::success('Password reset successful. You can now log in.');
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

        $otpChallengeData = $this->authService->resendOtp($validated['challenge_token']);

        return ApiResponse::success(
            'OTP resent successfully.',
            $otpChallengeData
        );
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
        $refreshData = $this->authService->refresh();

        return ApiResponse::success(
            'Token refreshed successfully.',
            $refreshData
        );
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

    /**
     * Test Send Phone OTP
     *
     * Test send the phone OTP
     *
     * @group Authentication
     * @authenticated
     */
    public function sendPhoneOtp()
    {
        $otpChallengeData = $this->authService->sendPhoneOtp();

        return ApiResponse::success(
            'OTP sent to your phone number.',
            $otpChallengeData
        );
    }
}