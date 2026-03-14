<?php

namespace App\Enums;

enum SignupSourceEnum: string
{
    case SELF = 'self';        // user signs up himself
    case ADMIN = 'admin';      // created by admin panel
    case SEEDER = 'seeder';    // created via database seeder

    case GOOGLE = 'google';
    case FACEBOOK = 'facebook';
}