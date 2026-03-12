<?php

namespace App\Services\Auth;

use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class TokenService
{
    public function issue(User $user): string
    {
        return JWTAuth::fromUser($user);
    }

    public function refresh(): string
    {
        return JWTAuth::parseToken()->refresh();
    }
}