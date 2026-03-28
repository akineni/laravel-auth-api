<?php

namespace App\Enums;

enum RoleModificationContextEnum: string
{
    case USER_CREATION = 'user_creation';
    case MANUAL_ASSIGNMENT = 'manual_assignment';
    case MANUAL_REVOCATION = 'manual_revocation';
    case ROLE_SYNC = 'role_sync';
}