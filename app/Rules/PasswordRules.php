<?php

namespace App\Rules;

use Illuminate\Validation\Rules\Password;

class PasswordRules
{
    public static function default(): array
    {
        return [
            'required',
            'confirmed',
            Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols()
                ->uncompromised()
        ];
    }

    public static function simple(): array
    {
        return [
            'required',
            'confirmed',
            Password::min(8),
        ];
    }
}
