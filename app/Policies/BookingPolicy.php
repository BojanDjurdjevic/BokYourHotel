<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    public function pay(User $user, Booking $booking): bool
    {
        return $booking->user_id === $user->id;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Booking $booking): bool
    {
        return $booking->user_id === $user->id || $this->manage($user, $booking);
    }

    public function manage(User $user, Booking $booking): bool
    {
        return $user->isAdmin() || (
            $user->isSupplier() && $booking->hotel->supplier_id === $user->id
        );
    }

    public function cancel(User $user, Booking $booking): bool
    {
        return $this->view($user, $booking);
    }

    public function confirm(User $user, Booking $booking): bool
    {
        return $this->manage($user, $booking);
    }

    public function complete(User $user, Booking $booking): bool
    {
        return $this->manage($user, $booking);
    }

    public function delete(User $user, Booking $booking): bool
    {
        return false;
    }

    public function forceDelete(User $user, Booking $booking): bool
    {
        return false;
    }
}
