<?php

namespace App\Support;

final class PublicLabel
{
    public static function clean(?string $value, string $fallback = ''): string
    {
        $value = trim((string) $value);
        $value = preg_replace('/^demo\s+/i', '', $value) ?? $value;

        return $value !== '' ? $value : $fallback;
    }
}
