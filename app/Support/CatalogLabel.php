<?php

namespace App\Support;

use Illuminate\Support\Str;

final class CatalogLabel
{
    public static function key(?string $value): string
    {
        $value = trim((string) $value);
        $value = preg_replace('/^demo\s+/i', '', $value) ?? $value;

        return Str::of($value)
            ->lower()
            ->replace(['&', '_', '-'], [' and ', ' ', ' '])
            ->squish()
            ->replace(' ', '_')
            ->toString();
    }

    public static function label(?string $value): string
    {
        return match (self::key($value)) {
            'room_only' => 'Room only',
            'breakfast' => 'Breakfast',
            'half_board' => 'Half Board',
            'full_board' => 'Full Board',
            'all_inclusive' => 'All Inclusive',
            'bed_and_breakfast' => 'Bed & Breakfast',
            default => Str::headline(str_replace('_', ' ', self::key($value))),
        };
    }
}
