<?php

namespace App\Services;

use App\Exceptions\BookingException;
use App\Models\Room;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BookingService {

    public function createBooking(Room $room, array $data) 
    {
        $this->validateDates($data);

        $inventories = $this->loadInventories(
            $room,
            $data['check_in'],
            $data['check_out']
        ); /*

        $this->ensureAvailability($inventories);

        $totalPrice = $this->calculatePrice($inventories, $data['rooms']);

        return DB::transaction(function () use (
            $room,
            $data,
            $inventories,
            $totalPrice
        ) {

            $booking = $this->createBookingRecord(
                $room,
                $data,
                $totalPrice
            ); 

            $this->decreaseAvailability($inventories);

            return $booking;
        });*/
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

    private function buildPeriod(array $data): Collection
    {
        $start = Carbon::parse($data['check_in']);
        $end = Carbon::parse($data['check_out']);

        $period = collect();

        for ($date = $start->copy(); $date < $end; $date->addDay()) {
            $period->push($date->copy());
        }

        return $period;
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

    private function ensureAvailability(Room $room, Collection $period, EloquentCollection $inventories, int $numRooms): void
    {
        foreach ($period as $date) {

            $inventory = $inventories->firstWhere(
                'date',
                $date->toDateString()
            );

            if (!$inventory) {

                $available = $room->total_units;

            } else {

                $available = $inventory->available;
            }

            if ($available < $numRooms) {
                throw new BookingException(
                    'There is no availability for requested period.'
                );
            }
        }
    }

    private function calculatePrice(EloquentCollection $inventories, int $numRooms): float
    {
        return $inventories->sum('price') * $numRooms;
    }

    private function createBookingRecord(Room $room, array $data)
    {
        
    }

    private function decreaseAvailability()
    {

    }


}