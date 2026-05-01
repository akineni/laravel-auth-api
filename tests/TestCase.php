<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tymon\JWTAuth\Facades\JWTAuth;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Clear Spatie permission cache between tests
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Log in a user and return an authenticated request with JWT bearer token.
     */
    protected function actingAsUser(User $user): static
    {
        $session = $user->authSessions()->create([
            'last_activity_at' => now(),
            'ip_address'       => '127.0.0.1',
            'user_agent'       => 'TestAgent',
        ]);

        $token = JWTAuth::claims(['sid' => $session->id])->fromUser($user);

        $this->withHeader('Authorization', "Bearer {$token}");

        return $this;
    }
}
