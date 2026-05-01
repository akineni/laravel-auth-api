<?php

namespace Tests\Feature\Auth;

use App\Data\Auth\OtpChallengeData;
use App\Enums\UserStatusEnum;
use App\Models\User;
use App\Services\OTP\SendOtpService;
use Tests\TestCase;

class AuthTest extends TestCase
{
    // -------------------------------------------------------
    // Registration
    // -------------------------------------------------------

    public function test_user_can_register_with_valid_data(): void
    {
        $this->mockOtpService();

        $response = $this->postJson('/api/v1/auth/register', [
            'firstname'             => 'John',
            'lastname'              => 'Doe',
            'email'                 => 'john@example.com',
            'password'              => 'T3st#Secure!XyZ9',
            'password_confirmation' => 'T3st#Secure!XyZ9',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('users', [
            'email'  => 'john@example.com',
            'status' => UserStatusEnum::PENDING->value,
        ]);
    }

    public function test_register_requires_all_fields(): void
    {
        $response = $this->postJson('/api/v1/auth/register', []);

        $response->assertStatus(422)
            ->assertJsonPath('status', 'error');
    }

    public function test_register_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'john@example.com']);

        $response = $this->postJson('/api/v1/auth/register', [
            'firstname'             => 'John',
            'lastname'              => 'Doe',
            'email'                 => 'john@example.com',
            'password'              => 'T3st#Secure!XyZ9',
            'password_confirmation' => 'T3st#Secure!XyZ9',
        ]);

        $response->assertStatus(422);
    }

    // -------------------------------------------------------
    // Login
    // -------------------------------------------------------

    public function test_user_can_login_with_valid_credentials(): void
    {
        User::factory()->create(['email' => 'john@example.com']);

        $response = $this->postJson('/api/v1/auth/login', [
            'login'    => 'john@example.com',
            'password' => 'T3st#Secure!XyZ9',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure(['data' => ['access_token', 'token_type', 'expires_in', 'user']]);
    }

    public function test_user_can_login_with_username(): void
    {
        User::factory()->create(['username' => 'johndoe']);

        $response = $this->postJson('/api/v1/auth/login', [
            'login'    => 'johndoe',
            'password' => 'T3st#Secure!XyZ9',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create(['email' => 'john@example.com']);

        $response = $this->postJson('/api/v1/auth/login', [
            'login'    => 'john@example.com',
            'password' => 'WrongPassword1!',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('status', 'error');
    }

    public function test_login_fails_for_inactive_user(): void
    {
        User::factory()->inactive()->create(['email' => 'john@example.com']);

        $response = $this->postJson('/api/v1/auth/login', [
            'login'    => 'john@example.com',
            'password' => 'T3st#Secure!XyZ9',
        ]);

        $response->assertStatus(403);
    }

    public function test_login_fails_for_locked_account(): void
    {
        User::factory()->locked()->create(['email' => 'john@example.com']);

        $response = $this->postJson('/api/v1/auth/login', [
            'login'    => 'john@example.com',
            'password' => 'T3st#Secure!XyZ9',
        ]);

        $response->assertStatus(423);
    }

    public function test_failed_logins_increment_on_wrong_password(): void
    {
        User::factory()->create(['email' => 'john@example.com']);

        $this->postJson('/api/v1/auth/login', [
            'login'    => 'john@example.com',
            'password' => 'WrongPassword1!',
        ]);

        $this->assertDatabaseHas('users', [
            'email'         => 'john@example.com',
            'failed_logins' => 1,
        ]);
    }

    public function test_account_locks_after_max_failed_attempts(): void
    {
        User::factory()->create(['email' => 'john@example.com']);

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/login', [
                'login'    => 'john@example.com',
                'password' => 'WrongPassword1!',
            ]);
        }

        $user = User::where('email', 'john@example.com')->first();

        $this->assertNotNull($user->locked_until);
        $this->assertTrue($user->locked_until->isFuture());
    }

    public function test_login_requires_login_and_password(): void
    {
        $response = $this->postJson('/api/v1/auth/login', []);

        $response->assertStatus(422);
    }

    // -------------------------------------------------------
    // Logout
    // -------------------------------------------------------

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsUser($user)
            ->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');
    }

    public function test_unauthenticated_user_cannot_logout(): void
    {
        $response = $this->postJson('/api/v1/auth/logout');

        $response->assertStatus(401);
    }

    // -------------------------------------------------------
    // Forgot Password
    // -------------------------------------------------------

    public function test_forgot_password_returns_success_even_for_unknown_email(): void
    {
        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'nonexistent@example.com',
        ]);

        // Safe enumeration — does not reveal whether email exists
        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');
    }

    public function test_forgot_password_requires_valid_email(): void
    {
        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'not-an-email',
        ]);

        $response->assertStatus(422);
    }

    // -------------------------------------------------------
    // Token Refresh
    // -------------------------------------------------------

    public function test_authenticated_user_can_refresh_token(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsUser($user)
            ->postJson('/api/v1/auth/refresh-token');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['access_token']]);
    }

    public function test_unauthenticated_user_cannot_refresh_token(): void
    {
        $response = $this->postJson('/api/v1/auth/refresh-token');

        $response->assertStatus(401);
    }

    // -------------------------------------------------------
    // Helpers
    // -------------------------------------------------------

    private function mockOtpService(): void
    {
        $stub = $this->createStub(SendOtpService::class);
        $stub->method('send')->willReturn(new OtpChallengeData(
            otpRequired: true,
            destination: 'j***@example.com',
            challengeToken: 'fake-challenge-token',
            expiresIn: 300,
        ));

        $this->app->instance(SendOtpService::class, $stub);
    }
}