<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\OTP\Generators\NumericOtpGenerator;
use App\Services\UsernameService;
use Tests\TestCase;

class OtpGeneratorTest extends TestCase
{
    public function test_generates_otp_with_correct_length(): void
    {
        $generator = new NumericOtpGenerator(6);
        $otp = $generator->generate();

        $this->assertSame(6, strlen($otp));
    }

    public function test_generated_otp_is_numeric(): void
    {
        $generator = new NumericOtpGenerator(6);
        $otp = $generator->generate();

        $this->assertMatchesRegularExpression('/^\d+$/', $otp);
    }

    public function test_generates_otp_with_custom_length(): void
    {
        $generator = new NumericOtpGenerator(8);
        $otp = $generator->generate();

        $this->assertSame(8, strlen($otp));
    }

    public function test_does_not_start_with_zero(): void
    {
        $generator = new NumericOtpGenerator(6);

        // Run multiple times to reduce flakiness
        for ($i = 0; $i < 20; $i++) {
            $otp = $generator->generate();
            $this->assertNotSame('0', $otp[0]);
        }
    }
}

class UsernameServiceTest extends TestCase
{
    private UsernameService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new UsernameService();
    }

    public function test_normalize_lowercases_username(): void
    {
        $this->assertSame('johndoe', $this->service->normalize('JohnDoe'));
    }

    public function test_normalize_strips_invalid_characters(): void
    {
        $this->assertSame('johndoe', $this->service->normalize('john doe!'));
    }

    public function test_normalize_allows_dots_and_underscores(): void
    {
        $this->assertSame('john.doe_99', $this->service->normalize('john.doe_99'));
    }

    public function test_exists_returns_false_for_nonexistent_username(): void
    {
        $result = $this->service->exists('nonexistent_user_xyz', User::class);

        $this->assertFalse($result);
    }

    public function test_exists_returns_true_for_taken_username(): void
    {
        User::factory()->create(['username' => 'takename']);

        $result = $this->service->exists('takename', User::class);

        $this->assertTrue($result);
    }

    public function test_generate_unique_returns_available_username(): void
    {
        $username = $this->service->generateUnique('John', 'Doe', User::class);

        $this->assertNotEmpty($username);
        $this->assertFalse($this->service->exists($username, User::class));
    }

    public function test_suggest_returns_requested_number_of_suggestions(): void
    {
        $suggestions = $this->service->suggest('johndoe', User::class, 5);

        $this->assertCount(5, $suggestions);
    }

    public function test_suggest_returns_only_available_usernames(): void
    {
        $suggestions = $this->service->suggest('johndoe', User::class, 3);

        foreach ($suggestions as $suggestion) {
            $this->assertFalse($this->service->exists($suggestion, User::class));
        }
    }
}