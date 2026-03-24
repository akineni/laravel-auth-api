<?php

namespace App\Services\Auth;

use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class TokenService
{
    public function __construct(
        private readonly AuthSessionService $authSessionService,
    ) {}

    public function issue(User $user): string
    {
        $session = $this->authSessionService->create($user, request());

        return JWTAuth::claims([
            'sid' => $session->id,
        ])->fromUser($user);
    }

    public function refresh(): string
    {
        $payload = JWTAuth::parseToken()->getPayload();
        $sessionId = $payload->get('sid');

        return JWTAuth::claims([
            'sid' => $sessionId,
        ])->refresh();
    }
}