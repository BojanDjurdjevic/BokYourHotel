<?php

namespace App\Support;

use Illuminate\Support\Str;

final class FacilityLabel
{
    private const LABELS = [
        'wifi' => 'Wi-Fi',
        'wi-fi' => 'Wi-Fi',
        'air condition' => 'Air conditioning',
        'air conditioning' => 'Air conditioning',
        'parking' => 'Parking',
        'pool' => 'Pool',
        'restaurant' => 'Restaurant',
        'spa' => 'Spa',
        'gym' => 'Gym',
        'bar' => 'Bar',
        'balcony' => 'Balcony',
        'bathtub' => 'Bathtub',
        'bath' => 'Bathtub',
        'city view' => 'City view',
        'sea view' => 'Sea view',
        'coffee machine' => 'Coffee machine',
        'minibar' => 'Minibar',
        'shower' => 'Shower',
        'tv' => 'TV',
        'airport transfer' => 'Airport transfer',
    ];

    public static function label(?string $value): string
    {
        $value = trim((string) $value);
        $value = preg_replace('/^demo\s+/i', '', $value) ?? $value;
        $key = Str::of($value)->lower()->replace('_', ' ')->replace('-', '-')->squish()->toString();

        return self::LABELS[$key] ?? Str::headline($value);
    }
}
