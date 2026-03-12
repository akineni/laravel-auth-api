<?php

namespace App\Traits;

use App\Models\User;
use Carbon\Carbon;

trait CompletesLogin
{
    protected function completeLogin(User $user): array
    {
        $this->updateLastLogin($user);

        $token = $this->generateLoginToken($user);

        return $this->buildLoginResponse($user, $token);
    }

    protected function updateLastLogin(User $user): void
    {
        $user->last_login = Carbon::now();
        $user->save();
    }

    protected function generateLoginToken(User $user): string
    {
        return $this->tokenService->issue($user);
    }

    protected function buildLoginResponse(User $user, string $token): array
    {
        return [
            'success' => true,
            'message' => 'Login successful',
            'data' => $this->formatTokenDataResponse($user, $token),
        ];
    }

    protected function formatTokenDataResponse(User $user, string $token): array
    {
        /** @var \Tymon\JWTAuth\JWTGuard $guard */
        $guard = auth();

        $user->loadMissing('roles');

        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $guard->factory()->getTTL() * 60,
            'user' => new \App\Http\Resources\UserMiniResource($user),
        ];
    }
}

