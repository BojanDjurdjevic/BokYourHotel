<?php

namespace App\Support;

use Illuminate\Support\Str;

final class FacilityLabel
{
    private const LABELS = [
        'wifi' => 'Wi-Fi',
        'air_conditioning' => 'Air conditioning',
        'parking' => 'Parking',
        'pool' => 'Pool',
        'restaurant' => 'Restaurant',
        'spa' => 'Spa',
        'gym' => 'Gym',
        'bar' => 'Bar',
        'balcony' => 'Balcony',
        'bathtub' => 'Bathtub',
        'city_view' => 'City view',
        'sea_view' => 'Sea view',
        'coffee_machine' => 'Coffee machine',
        'minibar' => 'Minibar',
        'shower' => 'Shower',
        'tv' => 'TV',
        'airport_transfer' => 'Airport transfer',
    ];

    public static function label(?string $value): string
    {
        $key = self::key($value);

        return self::LABELS[$key] ?? Str::headline(str_replace('_', ' ', $key));
    }

    public static function key(?string $value): string
    {
        $value = trim((string) $value);
        $value = preg_replace('/^demo\s+/i', '', $value) ?? $value;
        $value = Str::of($value)->lower()->replace(['_', '-'], ' ')->squish()->toString();

        return match ($value) {
            'wifi', 'wi fi', 'wi-fi' => 'wifi',
            'air condition', 'air conditioning' => 'air_conditioning',
            'bath', 'bathtub' => 'bathtub',
            default => str_replace(' ', '_', $value),
        };
    }

    public static function canonicalValue(?string $value): string
    {
        return self::label($value);
    }

    public static function aliases(?string $value): array
    {
        return match (self::key($value)) {
            'wifi' => ['wifi', 'WiFi', 'Wi-Fi', 'Demo WiFi', 'Demo Wi-Fi'],
            'air_conditioning' => ['air_condition', 'Air Conditioning', 'Air conditioning', 'Demo Air Conditioning', 'Demo Air conditioning'],
            'bathtub' => ['bath', 'Bathtub', 'Demo Bathtub'],
            'balcony' => ['balcony', 'Balcony', 'Demo Balcony'],
            default => array_values(array_unique([(string) $value, self::label($value), 'Demo '.self::label($value)])),
        };
    }
}
