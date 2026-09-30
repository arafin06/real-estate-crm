<?php

namespace App\Support;

/**
 * Single source of truth for phone number normalization, validation, and
 * display formatting. Storage is always digits-only (10-digit US numbers);
 * formatting for display/tel: links is derived on read, never stored.
 */
class PhoneNumber
{
    /**
     * Strip everything but digits, and drop a leading US country code (1)
     * when it results in a standard 10-digit number.
     */
    public static function normalize(?string $raw): ?string
    {
        if ($raw === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $raw) ?? '';

        if (strlen($digits) === 11 && str_starts_with($digits, '1')) {
            $digits = substr($digits, 1);
        }

        return $digits === '' ? null : $digits;
    }

    /**
     * A normalized value is valid only if it's a standard 10-digit US number.
     */
    public static function isValid(?string $raw): bool
    {
        $digits = self::normalize($raw);

        return $digits !== null && strlen($digits) === 10;
    }

    /**
     * (XXX) XXX-XXXX for a valid 10-digit number; falls back to the
     * normalized digits for anything else so display never crashes.
     */
    public static function format(?string $raw): ?string
    {
        $digits = self::normalize($raw);

        if ($digits === null) {
            return null;
        }

        if (strlen($digits) === 10) {
            return sprintf(
                '(%s) %s-%s',
                substr($digits, 0, 3),
                substr($digits, 3, 3),
                substr($digits, 6, 4)
            );
        }

        return $digits;
    }

    public static function telLink(?string $raw): ?string
    {
        $digits = self::normalize($raw);

        return $digits === null ? null : "tel:{$digits}";
    }
}
