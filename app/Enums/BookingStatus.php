<?php

namespace App\Enums;

enum BookingStatus:string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Cancelled = 'cancelled';
    case Rejected = 'rejected';
    case Expired = 'expired';

    public function isActive(): bool
    {
        return match ($this) {
            self::Pending,
            self::Confirmed => true,
            default => false,
        };
    }

    public function isFinal(): bool
    {
        return match ($this) {
            self::Cancelled,
            self::Rejected,
            self::Expired => true,
            default => false,
        };
    }
}
