<?php

namespace App\Enums;

enum UserStatusEnum: string
{
    case PENDING = 'pending';      // just registered, awaiting activation
    case ACTIVE = 'active';        // fully active
    case INACTIVE = 'inactive';    // manually deactivated
    case SUSPENDED = 'suspended';  // temporarily locked
}
