<?php

namespace Tests\Feature\Auth;

use App\Enums\OtpContextEnum;
use App\Enums\OtpMethodEnum;
use App\Models\AuthChallenge;
use App\Models\User;
use Tests\TestCase;

class OtpTest extends TestCase
{
    // -------------------------------------------------------
    // Verify OTP
    // -------------------------------------------------------

    public function test_user_can_verify_valid_otp(): void
    {
        $user = User::factory()->create();
        [$challenge, $otp] = $this->createChallenge($user, OtpContextEnum::EMAIL_VERIFICATION);

        $response = $this->postJson('/api/v1/auth/verify-otp', [
            'challenge_token' => $challenge->challenge_token,
            'otp'             => $otp,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');
    }

    public function test_verify_otp_fails_with_wrong_code(): void
    {
        $user = User::factory()->create();
        [$challenge] = $this->createChallenge($user, OtpContextEnum::EMAIL_VERIFICATION);

        $response = $this->postJson('/api/v1/auth/verify-otp', [
            'challenge_token' => $challenge->challenge_token,
            'otp'             => '000000',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('status', 'error');
    }

    public function test_verify_otp_fails_with_expired_challenge(): void
    {
        $user = User::factory()->create();
        [$challenge, $otp] = $this->createChallenge(
            $user,
            OtpContextEnum::EMAIL_VERIFICATION,
            expiresAt: now()->subMinutes(10)
        );

        $response = $this->postJson('/api/v1/auth/verify-otp', [
            'challenge_token' => $challenge->challenge_token,
            'otp'             => $otp,
        ]);

        $response->assertStatus(400);
    }

    public function test_verify_otp_fails_with_invalid_challenge_token(): void
    {
        $response = $this->postJson('/api/v1/auth/verify-otp', [
            'challenge_token' => 'invalid-token-that-does-not-exist',
            'otp'             => '123456',
        ]);

        $response->assertStatus(422);
    }

    public function test_login_otp_returns_access_token_on_success(): void
    {
        $user = User::factory()->create();
        [$challenge, $otp] = $this->createChallenge($user, OtpContextEnum::LOGIN);

        $response = $this->postJson('/api/v1/auth/verify-otp', [
            'challenge_token' => $challenge->challenge_token,
            'otp'             => $otp,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['access_token']]);
    }

    // -------------------------------------------------------
    // Resend OTP
    // -------------------------------------------------------

    public function test_user_can_resend_otp_after_cooldown(): void
    {
        $user = User::factory()->create();
        // Create challenge old enough to pass cooldown
        [$challenge] = $this->createChallenge(
            $user,
            OtpContextEnum::EMAIL_VERIFICATION,
            createdAt: now()->subMinutes(5)
        );

        $response = $this->postJson('/api/v1/auth/resend-otp', [
            'challenge_token' => $challenge->challenge_token,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');
    }

    public function test_resend_otp_requires_valid_challenge_token(): void
    {
        $response = $this->postJson('/api/v1/auth/resend-otp', [
            'challenge_token' => 'nonexistent-token',
        ]);

        $response->assertStatus(422);
    }

    // -------------------------------------------------------
    // Helpers
    // -------------------------------------------------------

    private function createChallenge(
        User $user,
        OtpContextEnum $context,
        ?\Carbon\Carbon $expiresAt = null,
        ?\Carbon\Carbon $createdAt = null
    ): array {
        $otp       = '123456';
        $expiresAt = $expiresAt ?? now()->addMinutes(5);

        $challenge = AuthChallenge::create([
            'user_id'         => $user->id,
            'challenge_token' => \Illuminate\Support\Str::random(64),
            'code'            => hash('sha256', $otp),
            'method'          => OtpMethodEnum::OTP_EMAIL->value,
            'context'         => $context->value,
            'expires_at'      => $expiresAt,
        ]);

        if ($createdAt) {
            $challenge->forceFill(['created_at' => $createdAt])->save();
        }

        return [$challenge, $otp];
    }
}