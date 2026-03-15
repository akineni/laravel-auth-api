<?php

namespace App\Enums;

enum TwoFactorMethodEnum: string
{
    case DEFAULT = 'default';
    case AUTHENTICATOR_APP = 'authenticator_app';
}