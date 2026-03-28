<?php

namespace App\Enums;

enum RoleEnum: string
{
    case SUPER_ADMIN = 'Super Admin';
    case ADMIN = 'Admin';

    /**
     * Get privileged administrative role names.
     *
     * @return array<int, string>
     */
    public static function adminRoles(): array
    {
        return [
            self::SUPER_ADMIN->value,
            self::ADMIN->value,
        ];
    }
}
