<?php

namespace App\Enums;

enum OtpMethodEnum: string
{
    case OTP_EMAIL = 'otp_email';
    case OTP_SMS = 'otp_sms';
    case TOTP = 'totp';
}