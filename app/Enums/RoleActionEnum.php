<?php

namespace App\Enums;

enum RoleActionEnum: string
{
    case ASSIGNED = 'assigned';
    case REVOKED = 'revoked';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::ASSIGNED => 'assigned',
            self::REVOKED => 'revoked',
        };
    }

    /**
     * Get verb phrase for sentences.
     */
    public function verb(): string
    {
        return match ($this) {
            self::ASSIGNED => 'assigned to',
            self::REVOKED => 'revoked from',
        };
    }
}