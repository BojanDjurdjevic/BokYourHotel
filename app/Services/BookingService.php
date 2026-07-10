<?php

namespace App\Services;

use App\Models\Room;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

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

    private function loadInventories(Room $room, Carbon $checkIn, Carbon $checkOut): EloquentCollection
    {
        return $room->inventories()
            ->whereBetween('date', [
                $checkIn,
                $checkOut->copy()->subDay()
            ])
            ->orderBy('date')
            ->get()
            ->keyBy(function ($inventory) {
                return $inventory->date->format('Y-m-d');
            });
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