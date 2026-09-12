<?php
namespace App\Enums;
enum BookingNoticeType: string {
    case BookingCreated = 'Booking created';
    case BookingConfirmed = 'Booking confirmed';
    case BookingCancelled = 'Booking cancelled';
    case PaymentSucceeded = 'Payment succeeded';
    case PaymentRefunded = 'Fake refund recorded';
    case BookingExpired = 'Booking expired';
}
