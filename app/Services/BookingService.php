<?php

namespace App\Services;

use App\Exceptions\BookingException;
use App\Models\BoardType;
use App\Models\Booking;
use App\Models\Room;
use App\Models\RoomInventory;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BookingService
{
    public function create(array $data): Booking
    {

    }

    private function validatePeriod(Carbon $checkIn, Carbon $checkOut): void
    {
        if ($checkIn->isPast()) {
            throw new BookingException(
                'Check-in date cannot be in the past.'
            );
        }

        if ($checkOut <= $checkIn) {
            throw new BookingException(
                'Check-out date must be after check-in.'
            );
        }

        if ($checkIn->diffInDays($checkOut) > 30) {
            throw new BookingException(
                'The maximum stay is 30 days.'
            );
        }
    }

    private function buildPeriod(Carbon $checkIn, Carbon $checkOut): Collection
    {
        $period = collect();

        for ($date = $checkIn->copy(); $date < $checkOut; $date->addDay()) {
            $period->push($date->copy());
        }

        return $period;
    }

    private function loadRooms(array $items): EloquentCollection
    {
        $roomIds = collect($items)
            ->pluck('room_id')
            ->unique();

        return Room::query()
            ->whereIn('id', $roomIds)
            ->with('boardTypes')
            ->get()
            ->keyBy('id');
    }

    private function loadInventories(EloquentCollection $rooms, Collection $period): Collection
    {
        $inventories = RoomInventory::query()
        ->whereIn('room_id', $rooms->pluck('id'))
        ->whereBetween('date', [$period->first()->toDateString(), $period->last()->toDateString()])
        ->get()
        ->groupBy('room_id')
        ->map(function ($items) {
            return $items->keyBy(function ($inventory) {
                return $inventory->date->format('Y-m-d');
            });
        });

        return $inventories;
    }

    private function ensureRoomsBelongToHotel(EloquentCollection $rooms, int $hotelId): void
    {
        if(!$rooms->every(
            fn($room) => $room->hotel_id === $hotelId 
        )) {
            throw new BookingException('Selected rooms do not belong to this hotel!');
        }
    }

    private function findBoardType(Room $room, int $boardTypeId): BoardType
    {
        $boardType = $room->boardTypes
            ->firstWhere('id', $boardTypeId);

        if (! $boardType) {
            throw new BookingException(
                'Selected board type does not belong to selected room.'
            );
        }

        return $boardType;
    }

    private function ensureAvailability(EloquentCollection $rooms, Collection $inventories, Collection $period, array $items): void
    {
        foreach ($items as $item) {

            $room = $rooms[$item['room_id']];

            $roomInventories = $inventories[$room->id] ?? collect();

            foreach ($period as $date) {

                $inventory = $roomInventories
                    ->get($date->toDateString());

                $available = $inventory
                    ? $inventory->available
                    : $room->total_units;

                if ($available < $item['quantity']) {

                    throw new BookingException(
                        "Room {$room->name} does not have enough availability."
                    );

                }
            }
        }
    }

    private function calculateTotals(EloquentCollection $rooms, array $items, Collection $period): array
    {

    }

    private function createBooking(array $data, array $totals): Booking
    {

    }

    private function createBookingItems(Booking $booking, EloquentCollection $rooms, array $items, Collection $period): void
    {

    }

    private function decreaseAvailability(Collection $inventories, Collection $period, array $items): void
    {

    }

    private function generateBookingNumber(): string
    {
        
    }
}