<?php

namespace App\Services;

use App\Models\Room;
use Carbon\Carbon;
use Exception;

class BookingService {

    public function createBooking(Room $room, array $data) 
    {

    }

    private function validateDates(array $data): void
    {
        $start = Carbon::parse($data['check_in']);
        $end = Carbon::parse($data['check_out']);

        if($start->isPast()) {
            throw new Exception('Check-in date cannot be in the past.');
        } 

        if($end <= $start) {
            throw new Exception('Check-out date must be after check-in.');
        }

        if($start->diffInDays($end) > 30) {
            throw new Exception('The maximum stay is 30 days.');
        }
    }

    public function loadInventories()
    {

    }

    public function ensureAvailability()
    {

    }

    public function calculatePrice()
    {

    }

    public function createBookingRecord()
    {

    }

    public function decreaseAvailability()
    {

    }


}