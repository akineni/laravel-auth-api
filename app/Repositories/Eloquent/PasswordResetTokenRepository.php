<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\PasswordResetTokenRepositoryInterface;
use Illuminate\Support\Facades\Password;

class PasswordResetTokenRepository implements PasswordResetTokenRepositoryInterface
{
    public function create(User $user): string
    {
        return Password::createToken($user);
    }

    public function exists(User $user, string $token): bool
    {
        return Password::tokenExists($user, $token);
    }

    public function delete(User $user): bool
    {
        Password::deleteToken($user);

        return true;
    }
}