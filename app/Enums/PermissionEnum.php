<?php

namespace App\Enums;

enum PermissionEnum: string
{
    case USER_MANAGEMENT_VIEW = 'user_management.view';
    case USER_MANAGEMENT_CREATE = 'user_management.create';
    case USER_MANAGEMENT_EDIT = 'user_management.edit';
    case USER_MANAGEMENT_DELETE = 'user_management.delete';
}
