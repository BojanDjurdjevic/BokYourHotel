<?php

namespace App\Enums;

enum BookingStatus:string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Cancelled = 'cancelled';
    case Rejected = 'rejected';
    case Expired = 'expired';
}
