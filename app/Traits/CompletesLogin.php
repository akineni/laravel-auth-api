<?php

namespace App\Traits;

use App\Models\User;
use Carbon\Carbon;

trait CompletesLogin
{
    protected function finalizeLogin(User $user): array
    {
        $this->updateLastLogin($user);

        $token = $this->generateLoginToken($user);

        return $this->formatTokenDataResponse($user, $token);
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