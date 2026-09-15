<?php

namespace App\Support;

class PhoneNumberNormalizer
{
    private const NIGERIA_DIAL_CODE = '234';

    private const NIGERIA_COUNTRY_NAMES = ['nigeria', 'ng'];

    /**
     * Normalize a phone number to the dialable international format SMS
     * providers expect (e.g. Termii), when the user's country is known to
     * be Nigeria and the number doesn't already carry a country code.
     *
     * "08012345678"  -> "2348012345678"
     * "8012345678"   -> "2348012345678"
     * "+234 801 234 5678" -> "2348012345678"
     *
     * Numbers for any other (or unknown) country are returned unchanged,
     * since we have no reliable dialing convention to apply.
     */
    public static function normalize(?string $phone, ?string $country): ?string
    {
        if (! $phone || ! self::isNigeria($country)) {
            return $phone;
        }

        $digits = preg_replace('/\D+/', '', $phone);

        if (! $digits) {
            return $phone;
        }

        if (str_starts_with($digits, self::NIGERIA_DIAL_CODE)) {
            return $digits;
        }

        if (str_starts_with($digits, '0')) {
            return self::NIGERIA_DIAL_CODE . substr($digits, 1);
        }

        return self::NIGERIA_DIAL_CODE . $digits;
    }

    private static function isNigeria(?string $country): bool
    {
        return in_array(strtolower(trim((string) $country)), self::NIGERIA_COUNTRY_NAMES, true);
    }
}
