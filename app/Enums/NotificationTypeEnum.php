<?php

namespace App\Enums;

enum NotificationTypeEnum: string
{
    case USER_ROLE_MODIFIED = 'user_role_modified';
    case ROLE_ASSIGNED = 'role_assigned';
    case ROLE_REVOKED = 'role_revoked';
    case LOGIN_DETECTED = 'login_detected';
    case PASSWORD_CHANGED = 'password_changed';
    case SECURITY_ALERT = 'security_alert';

    /**
     * Get human-readable label for the notification type.
     */
    public function label(): string
    {
        return match ($this) {
            self::USER_ROLE_MODIFIED => 'User role modified',
            self::ROLE_ASSIGNED => 'Role assigned',
            self::ROLE_REVOKED => 'Role revoked',
            self::LOGIN_DETECTED => 'New Login detected',
            self::PASSWORD_CHANGED => 'Password changed',
            self::SECURITY_ALERT => 'Security alert',
        };
    }

    /**
     * Get default severity level for the notification type.
     */
    public function severity(): string
    {
        return match ($this) {
            self::LOGIN_DETECTED => 'warning',
            self::PASSWORD_CHANGED => 'warning',
            self::ROLE_REVOKED => 'warning',
            self::ROLE_ASSIGNED => 'info',
            self::USER_ROLE_MODIFIED => 'info',
            self::SECURITY_ALERT => 'warning',
        };
    }

    /**
     * Create enum from string safely.
     */
    public static function fromValue(?string $value): ?self
    {
        return $value ? self::tryFrom($value) : null;
    }
}