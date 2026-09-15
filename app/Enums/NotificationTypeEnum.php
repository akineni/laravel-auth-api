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
    case TWO_FA_ENABLED = 'two_fa_enabled';
    case TWO_FA_DISABLED = 'two_fa_disabled';
    case RECOVERY_CODES_REGENERATED = 'recovery_codes_regenerated';
    case ACCOUNT_DEACTIVATED = 'account_deactivated';
    case ACCOUNT_DELETED = 'account_deleted';
    case PROFILE_UPDATED = 'profile_updated';
    case ACCOUNT_LOCKED = 'account_locked';
    case ROLE_PERMISSIONS_UPDATED = 'role_permissions_updated';
    case ROLE_DELETED = 'role_deleted';

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
            self::TWO_FA_ENABLED => 'Two-factor authentication enabled',
            self::TWO_FA_DISABLED => 'Two-factor authentication disabled',
            self::RECOVERY_CODES_REGENERATED => 'Recovery codes regenerated',
            self::ACCOUNT_DEACTIVATED => 'Account deactivated',
            self::ACCOUNT_DELETED => 'Account deleted',
            self::PROFILE_UPDATED => 'Profile updated',
            self::ACCOUNT_LOCKED => 'Account locked',
            self::ROLE_PERMISSIONS_UPDATED => 'Role permissions updated',
            self::ROLE_DELETED => 'Role deleted',
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
            self::TWO_FA_ENABLED => 'info',
            self::TWO_FA_DISABLED => 'warning',
            self::RECOVERY_CODES_REGENERATED => 'info',
            self::ACCOUNT_DEACTIVATED => 'warning',
            self::ACCOUNT_DELETED => 'warning',
            self::PROFILE_UPDATED => 'info',
            self::ACCOUNT_LOCKED => 'warning',
            self::ROLE_PERMISSIONS_UPDATED => 'warning',
            self::ROLE_DELETED => 'warning',
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